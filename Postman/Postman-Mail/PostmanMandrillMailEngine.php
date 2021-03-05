<?php
if ( ! class_exists( 'PostmanMandrillMailEngine' ) ) {

	/**
	 * Sends mail with Mandrill API
	 * https://mandrillapp.com/api/docs/messages.php.html
	 *
	 * @author jasonhendriks
	 */
	class PostmanMandrillMailEngine implements PostmanMailEngine {

		// logger for all concrete classes - populate with setLogger($logger)
		protected $logger;

		// the result
		private $transcript;

		private $apiKey;
		private $mandrillMessage;

		function __construct( $apiKey ) {
			assert( ! empty( $apiKey ) );
			$this->apiKey = $apiKey;

			// create the logger
			$this->logger = new PostmanLogger( get_class( $this ) );

			// create the Message
			$this->mandrillMessage = array(
					'to' => array(),
					'headers' => array(),
			);
		}

		/**
		 * 		 * (non-PHPdoc)
		 * 		 *
		 *
		 * @see PostmanSmtpEngine::send()
		 *
		 * @return void
		 */
		public function send( PostmanMessage $message ) {
			$options = PostmanOptions::getInstance();

			// add the Postman signature - append it to whatever the user may have set
			if ( ! $options->isStealthModeEnabled() ) {
				$pluginData = apply_filters( 'postman_get_plugin_metadata', null );
				$this->addHeader( 'X-Mailer', sprintf( 'Postman SMTP %s for WordPress (%s)', $pluginData ['version'], 'https://wordpress.org/plugins/post-smtp/' ) );
			}

			// add the headers - see http://framework.zend.com/manual/1.12/en/zend.mail.additional-headers.html
			foreach ( ( array ) $message->getHeaders() as $header ) {
				$this->logger->debug( sprintf( 'Adding user header %s=%s', $header ['name'], $header ['content'] ) );
				$this->addHeader( $header ['name'], $header ['content'], true );
			}

			// if the caller set a Content-Type header, use it
			$contentType = $message->getContentType();
			if ( ! empty( $contentType ) ) {
				$this->logger->debug( 'Adding content-type ' . $contentType );
				$this->addHeader( 'Content-Type', $contentType );
			}

			// add the From Header
			$sender = $message->getFromAddress();
			{
				$senderEmail = PostmanOptions::getInstance()->getMessageSenderEmail();
				$senderName = $sender->getName();
				assert( ! empty( $senderEmail ) );
				$this->mandrillMessage ['from_email'] = $senderEmail;
			if ( ! empty( $senderName ) ) {
				$this->mandrillMessage ['from_name'] = $senderName;
			}
				// now log it
				$sender->log( $this->logger, 'From' );
			}

			// add the Sender Header, overriding what the user may have set
			$this->addHeader( 'Sender', $options->getEnvelopeSender() );

			// add the to recipients
			foreach ( ( array ) $message->getToRecipients() as $recipient ) {
				$recipient->log( $this->logger, 'To' );
				$recipient = array(
						'email' => $recipient->getEmail(),
						'name' => $recipient->getName(),
						'type' => 'to',
				);
				$this->mandrillMessage ['to'][] = $recipient;
			}

			// add the cc recipients
			foreach ( ( array ) $message->getCcRecipients() as $recipient ) {
				$recipient->log( $this->logger, 'Cc' );
				$recipient = array(
						'email' => $recipient->getEmail(),
						'name' => $recipient->getName(),
						'type' => 'cc',
				);
				$this->mandrillMessage ['to'][] = $recipient;
			}

			// add the bcc recipients
			foreach ( ( array ) $message->getBccRecipients() as $recipient ) {
				$recipient->log( $this->logger, 'Bcc' );
				$recipient = array(
						'email' => $recipient->getEmail(),
						'name' => $recipient->getName(),
						'type' => 'bcc',
				);
				$this->mandrillMessage ['to'][] = $recipient;
			}

			// add the reply-to
			$replyTo = $message->getReplyTo();
			// $replyTo is null or a PostmanEmailAddress object
			if ( isset( $replyTo ) ) {
				$this->addHeader( 'reply-to', $replyTo->format() );
			}

			// add the date
			$date = $message->getDate();
			if ( ! empty( $date ) ) {
				$this->addHeader( 'date', $message->getDate() );
			}

			// add the messageId
			$messageId = $message->getMessageId();
			if ( ! empty( $messageId ) ) {
				$this->addHeader( 'message-id', $messageId );
			}

			// add the subject
			if ( null !== $message->getSubject() ) {
				$this->mandrillMessage ['subject'] = $message->getSubject();
			}

			// add the message content
			{
				$textPart = $message->getBodyTextPart();
			if ( ! empty( $textPart ) ) {
				$this->logger->debug( 'Adding body as text' );
				$this->mandrillMessage ['text'] = $textPart;
			}
				$htmlPart = $message->getBodyHtmlPart();
			if ( ! empty( $htmlPart ) ) {
				$this->logger->debug( 'Adding body as html' );
				$this->mandrillMessage ['html'] = $htmlPart;
			}
			}

			// add attachments
			$this->logger->debug( 'Adding attachments' );
			$this->addAttachmentsToMail( $message );

			try {
				if ( $this->logger->isDebug() ) {
					$this->logger->debug( 'Creating Mandrill service with apiKey=' . $this->apiKey );
				}
				$mandrill = new Mandrill( $this->apiKey );

				// send the message
				if ( $this->logger->isDebug() ) {
					$this->logger->debug( 'Sending mail' );
				}

				if(property_exists($mandrill, 'messages')){
					$result = $mandrill->messages->send( $this->mandrillMessage );
				}else{
					throw new Exception('Could not send. Mandrill did not instantiate \'messages\' property.');
				}
				if ( $this->logger->isInfo() ) {
					$this->logger->info( sprintf( 'Message %d accepted for delivery', PostmanState::getInstance()->getSuccessfulDeliveries() + 1 ) );
				}

				$this->transcript = print_r( $result, true );
				$this->transcript .= PostmanModuleTransport::RAW_MESSAGE_FOLLOWS;
				$this->transcript .= print_r( $this->mandrillMessage, true );
			} catch ( Exception $e ) {
				$this->transcript = $e->getMessage();
				$this->transcript .= PostmanModuleTransport::RAW_MESSAGE_FOLLOWS;
				$this->transcript .= print_r( $this->mandrillMessage, true );
				throw $e;
			}
		}
		/**
		 * @param string $value
		 */
		private function addHeader( string $key, $value, bool $append = false ): void {
			$this->logger->debug( 'Adding header: ' . $key . ' = ' . $value );
			$this->mandrillMessage ['headers'];
			$header [ $key ] = $append && ! empty( $header [ $key ] ) ? $header [ $key ] . ', ' . $value : $value;
		}

		private function addAttachmentsToMail( PostmanMessage $message ): void {
			$attachments = $message->getAttachments();
			if ( isset( $attachments ) ) {
				$this->mandrillMessage ['attachments'] = array();
				$attArray = is_array( $attachments ) ? $attachments : explode( PHP_EOL, $attachments );
				// otherwise WordPress sends an array
				foreach ( $attArray as $file ) {
					if ( ! empty( $file ) ) {
						$this->logger->debug( 'Adding attachment: ' . $file );
						$attachment = array(
								'type' => 'attachment',
								'name' => basename( $file ),
								'content' => base64_encode( file_get_contents( $file ) ),
						);
						$this->mandrillMessage ['attachments'][] = $attachment;
					}
				}
			}
		}

		// return the SMTP session transcript
		public function getTranscript() {
			return $this->transcript;
		}
	}
}

