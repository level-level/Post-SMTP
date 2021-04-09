<?php

use Laminas\Mail\Header\HeaderInterface;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;
use PHPMailer\PHPMailer\Exception;

/**
 * Moved this code into a class so it could be used by both wp_mail() and PostmanSendTestEmailController
 *
 * @author jasonhendriks
 */
class PostmanWpMail {
	private $exception;
	private $transcript;
	private $totalTime;
	private $logger;

	/**
	 * 		 * Load the dependencies
	 */
	public function init(): void {
		$this->logger = new PostmanLogger( get_class( $this ) );
	}

	/**
	 * 		 * This methods follows the wp_mail function interface, but implements it Postman-style.
	 * 		 * Exceptions are held for later inspection.
	 * 		 * An instance of PostmanState updates the success/fail tally.
	 * 		 *
	 *
	 * @param mixed $to
	 * @param mixed $subject
	 * @param mixed $headers
	 * @param string[] $attachments
	 * @param string $message
	 *
	 * @return boolean
	 */
	public function send( $to, $subject, $message, $headers = '', $attachments = array() ) {

		// initialize for sending
		$this->init();

		// build the message
		$postmanMessage = $this->processWpMailCall( $to, $subject, $message, $headers, $attachments );

		// build the email log entry
		$log = new PostmanEmailLog();
		$log->originalTo = $to;
		$log->originalSubject = $subject;
		$log->originalMessage = $message;
		$log->originalHeaders = $headers;

		// send the message and return the result
		return $this->sendMessage( $postmanMessage, $log );
	}

	private function apply_default_headers( Message $message ): void {
		$message->getHeaders()->addHeaderLine('Message-ID', $this->createMessageId());
	}

	/**
	 * Creates the Message-ID
	 *
	 * @return string
	 */
	public function createMessageId() {

		$id = md5(uniqid((string)time()));

		$hostName = isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : php_uname('n');

		return $id . '@' . $hostName;
	}

	/**
	 * 		 * Builds a PostmanMessage based on the WordPress wp_mail parameters
	 * 		 *
	 *
	 * @param mixed $to
	 * @param mixed $subject
	 * @param mixed $message
	 * @param mixed $headers
	 * @param string[] $attachments Paths to files to attach
	 */
	private function processWpMailCall( $to, $subject, $message, $headers, $attachments ): Message {
		$this->logger->trace( 'wp_mail parameters before applying WordPress wp_mail filter:' );
		$this->traceParameters( $to, $subject, $message, $headers, $attachments );

		/**
		 * Filter the wp_mail() arguments.
		 *
		 * @since 1.5.4
		 *
		 * @param array $args
		 *        	A compacted array of wp_mail() arguments, including the "to" email,
		 *        	subject, message, headers, and attachments values.
		 */
		$atts = apply_filters( 'wp_mail', ['to' => $to, 'subject' => $subject, 'message' => $message, 'headers' => $headers, 'attachments' => $attachments] );
		if ( isset( $atts ['to'] ) ) {
			$to = $atts ['to'];
		}

		if ( isset( $atts ['subject'] ) ) {
			$subject = $atts ['subject'];
		}

		if ( isset( $atts ['message'] ) ) {
			$message = $atts ['message'];
		}

		if ( isset( $atts ['headers'] ) ) {
			$headers = $atts ['headers'];
		}

		if ( isset( $atts ['attachments'] ) ) {
			$attachments = $atts ['attachments'];
		}

		if ( ! is_array( $attachments ) ) {
			$attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
		}

		$this->logger->trace( 'wp_mail parameters after applying WordPress wp_mail filter:' );
		$this->traceParameters( $to, $subject, $message, $headers, $attachments );

		// Postman API: register the response hook
		add_filter( 'postman_wp_mail_result', function () : array {
			return $this->postman_wp_mail_result();
		} );

		// create the message
		$postmanMessage = $this->createNewMessage();
		$this->populateMessageFromWpMailParams( $postmanMessage, $to, $subject, $message, $headers, $attachments );

		// return the message
		return $postmanMessage;
	}

	/**
	 * Creates a new instance of PostmanMessage with a pre-set From and Reply-To
	 */
	public function createNewMessage() : Message {
		$message = new Message();
		$options = PostmanOptions::getInstance();
		// the From is set now so that it can be overridden
		$transport = PostmanTransportRegistry::getInstance()->getActiveTransport();
		$message->setFrom( $transport->getFromEmailAddress(), $transport->getFromName() );
		// the Reply-To is set now so that it can be overridden
		$message->setReplyTo( $options->getReplyTo() );
		$message->setEncoding( get_bloginfo( 'charset' ) );
		return $message;
	}

	/**
	 * A convenient place for any code to inject a constructed PostmanMessage
	 * (for example, from MyMail)
	 *
	 * The body parts may be set already at this time.
	 *
	 * @return boolean
	 */
	public function sendMessage( Message $message, PostmanEmailLog $log ) {

		$this->apply_default_headers( $message );

		// get the Options and AuthToken
		$options = PostmanOptions::getInstance();
		$authorizationToken = PostmanOAuthToken::getInstance();

		// get the transport and create the transportConfig and engine
		$transport = PostmanTransportRegistry::getInstance()->getActiveTransport();

		// create the Mail Engine
		$engine = $transport->createMailEngine();

		// add plugin-specific attributes to PostmanMessage
		$headers = $options->getAdditionalHeaders();
		if(!is_array($headers) && !empty($headers)){
			$headers = array($headers);
		}
		if(!empty($headers)){
			$message->getHeaders()->addHeaders( $headers );
		}
		if(!empty($options->getForcedToRecipients())){
			$message->addTo( $options->getForcedToRecipients() );
		}
		if(!empty($options->getForcedCcRecipients())){
			$message->addCc( $options->getForcedCcRecipients() );
		}
		if(!empty($options->getForcedBccRecipients())){
			$message->addBcc( $options->getForcedBccRecipients() );
		}

		// apply the WordPress filters
		// may impact the from address, from email, charset and content-type
		$message = $this->applyFilters($message);
		//do_action_ref_array( 'phpmailer_init', array( &$message ) );

		// create the body parts (if they are both missing) // TODO!
		// if ( $message->isBodyPartsEmpty() ) {
		// 	$message->createBodyParts();
		// }

		// is this a test run?
		$testMode = apply_filters( 'postman_test_email', false );
		if ( $this->logger->isDebug() ) {
			$this->logger->debug( 'testMode=' . $testMode );
		}

		// start the clock
		$startTime = microtime( true ) * 1000;

		try {

			// prepare the message
			// $message->validate( $transport ); // @TODO

			// send the message
			if ( $options->getRunMode() == PostmanOptions::RUN_MODE_PRODUCTION ) {
				if ( $transport->isLockingRequired() ) {
					PostmanUtils::lock();
					// may throw an exception attempting to contact the OAuth2 provider
					$this->ensureAuthtokenIsUpdated( $transport, $options, $authorizationToken );
				}

				$this->logger->debug( 'Sending mail' );
				// may throw an exception attempting to contact the SMTP server
				$engine->send( $message );

				// increment the success counter, unless we are just tesitng
				if ( ! $testMode ) {
					PostmanState::getInstance()->incrementSuccessfulDelivery();
				}
			}

			// clean up
			$this->postSend( $engine, $startTime, $options, $transport );

			if ( $options->getRunMode() == PostmanOptions::RUN_MODE_PRODUCTION || $options->getRunMode() == PostmanOptions::RUN_MODE_LOG_ONLY ) {
				// log the successful delivery
				PostmanEmailLogService::getInstance()->writeSuccessLog( $log, $message, '', $transport );
			}

			// return successful
			return true;
		} catch ( Exception $e ) {
			// save the error for later
			$this->exception = $e;

				// write the error to the PHP log
			$this->logger->error( get_class( $e ) . ' code=' . $e->getCode() . ' message=' . trim( $e->getMessage() ) );

			// increment the failure counter, unless we are just tesitng
			if ( ! $testMode && $options->getRunMode() == PostmanOptions::RUN_MODE_PRODUCTION ) {
				PostmanState::getInstance()->incrementFailedDelivery();
			}

			// clean up
			$this->postSend( $engine, $startTime, $options, $transport );

			if ( $options->getRunMode() == PostmanOptions::RUN_MODE_PRODUCTION || $options->getRunMode() == PostmanOptions::RUN_MODE_LOG_ONLY ) {
				// log the failed delivery
				PostmanEmailLogService::getInstance()->writeFailureLog( $log, $message, $engine->getTranscript(), $transport, $e->getMessage() );
			}

			// Fallback
			if ( $this->fallback( $log, $message, $options ) ) {

				return true;

			}

			$mail_error_data = array(
				'to' => $message->getTo()->current()->toString(),
				'subject' => $message->getSubject(),
				'message' => $message->getBody(),
				'headers' => $message->getHeaders(),
				'attachments' => $message->toString()
			);
			$mail_error_data['phpmailer_exception_code'] = $e->getCode();

			do_action( 'wp_mail_failed', new WP_Error( 'wp_mail_failed', $e->getMessage(), $mail_error_data ) );

			// return failure
			if ( PostmanOptions::getInstance()->getSmtpMailer() == 'phpmailer' ) {
				throw new Exception($e->getMessage(), $e->getCode());
			}
			return false;

		}
	}

	/**
	 * 		 * Apply the WordPress filters to the email
	 */
	public function applyFilters(Message $message): Message {
		if ( $this->logger->isDebug() ) {
			$this->logger->debug( 'Applying WordPress filters' );
		}

		$original_from = $message->getFrom()->current();
		/**
		 * Filter the email address to send from.
		 *
		 * @since 2.2.0
		 *
		 * @param string $from_email
		 *        	Email address to send from.
		 */
		$filteredEmail = apply_filters( 'wp_mail_from', $original_from->getEmail() );
		if ( $this->logger->isTrace() ) {
			$this->logger->trace( 'wp_mail_from: ' . $filteredEmail );
		}
		if ( $original_from->getEmail() !== $filteredEmail ) {
			$this->logger->debug( sprintf( 'Filtering From email address: before=%s after=%s', $original_from->getEmail(), $filteredEmail ) );
			$message->setFrom($filteredEmail, $original_from->getName());
		}

		$original_from = $message->getFrom()->current();
		/**
		 * Filter the name to associate with the "from" email address.
		 *
		 * @since 2.3.0
		 *
		 * @param string $from_name
		 *        	Name associated with the "from" email address.
		 */
		$filteredName = apply_filters( 'wp_mail_from_name', $original_from->getName() );
		if ( $this->logger->isTrace() ) {
			$this->logger->trace( 'wp_mail_from_name: ' . $filteredName );
		}
		if ( $original_from->getName() !== $filteredName ) {
			$this->logger->debug( sprintf( 'Filtering From email name: before=%s after=%s', $original_from->getName(), $filteredName ) );
			$message->setFrom($original_from->getEmail(), $filteredName);
		}

		/**
		 * Filter the default wp_mail() charset.
		 *
		 * @since 2.3.0
		 *
		 * @param string $charset
		 *        	Default email charset.
		 */
		$filteredCharset = apply_filters( 'wp_mail_charset', $message->getEncoding() );
		if ( $this->logger->isTrace() ) {
			$this->logger->trace( 'wp_mail_charset: ' . $filteredCharset );
		}
		if ( $message->getEncoding() !== $filteredCharset ) {
			$this->logger->debug( sprintf( 'Filtering Charset: before=%s after=%s', $message->getEncoding(), $filteredCharset ) );
			$message->setEncoding( $filteredCharset );
		}

		// Postman has it's own 'user override' filter
		$options = PostmanOptions::getInstance();
		$forcedEmailAddress = $options->getMessageSenderEmail();
		$original_from = $message->getFrom()->current();
		if ( $options->isSenderEmailOverridePrevented() && $original_from->getEmail() !== $forcedEmailAddress ) {
			$this->logger->debug( sprintf( 'Forced From email address: before=%s after=%s', $original_from->getEmail(), $forcedEmailAddress ) );
			$message->setFrom($forcedEmailAddress, $original_from->getName() );
		}

		$original_from = $message->getFrom()->current();
		if ( $options->is_fallback ) {
			$fallback_email = $options->getFallbackFromEmail();
			$this->logger->debug( sprintf( 'Fallback: Forced From email address: before=%s after=%s', $original_from->getEmail(), $fallback_email ) );
			$message->setFrom($fallback_email, $original_from->getName() );

		}

		$original_from = $message->getFrom()->current();
		$forcedEmailName = $options->getMessageSenderName();
		if ( $options->isSenderNameOverridePrevented() && $original_from->getName() !== $forcedEmailName ) {
			$this->logger->debug( sprintf( 'Forced From email name: before=%s after=%s', $original_from->getName(), $forcedEmailName ) );
			$message->setFrom($original_from->getEmail(), $forcedEmailName);
		}

		return $message;
	}

	/**
	 * @return bool
	 */
	private function fallback( PostmanEmailLog $log, Message $postMessage,PostmanOptions $options ) {

		if ( ! $options->is_fallback && $options->getFallbackIsEnabled() && $options->getFallbackIsEnabled() == 'yes' ) {

			$options->is_fallback = true;

			$status = $this->sendMessage( $postMessage, $log );

			$options->is_fallback = false;

			return $status;

		} else {
			$options->is_fallback = false;
		}

		return false;
	}

	/**
	 * 		 * Clean up after sending the mail
	 * 		 *
	 *
	 * @param TransportInterface $engine
	 * @param mixed               $startTime
	 */
	private function postSend( TransportInterface $engine, $startTime, PostmanOptions $options, PostmanModuleTransport $transport ): void {
		// delete the semaphore
		if ( $transport->isLockingRequired() ) {
			PostmanUtils::unlock();
		}

		// stop the clock
		$endTime = microtime( true ) * 1000;
		$this->totalTime = $endTime - $startTime;
	}

	/**
	 * 		 * Returns the result of the last call to send()
	 * 		 *
	 *
	 * @return array NULL
	 *
	 * @psalm-return array{time: mixed, exception: mixed, transcript: mixed}
	 */
	function postman_wp_mail_result(): array {
		return array(
				'time' => $this->totalTime,
				'exception' => $this->exception,
				'transcript' => $this->transcript,
		);
	}

	private function ensureAuthtokenIsUpdated( PostmanModuleTransport $transport, PostmanOptions $options, PostmanOAuthToken $authorizationToken ): void {
		assert( ! empty( $transport ) );
		assert( ! empty( $options ) );
		assert( ! empty( $authorizationToken ) );
		// ensure the token is up-to-date
		$this->logger->debug( 'Ensuring Access Token is up-to-date' );
		// interact with the Authentication Manager
		$wpMailAuthManager = PostmanAuthenticationManagerFactory::getInstance()->createAuthenticationManager();
		if ( $wpMailAuthManager->isAccessTokenExpired() ) {
			$this->logger->debug( 'Access Token has expired, attempting refresh' );
			$wpMailAuthManager->refreshToken();
			$authorizationToken->save();
		}
	}

	/**
	 * 		 * Aggregates all the content into a Message to be sent to the MailEngine
	 * 		 *
	 *
	 * @param mixed $to
	 * @param mixed $subject
	 * @param mixed $body
	 * @param mixed $headers
	 * @param mixed $attachments
	 */
	private function populateMessageFromWpMailParams( Message $message, $to, $subject, $body, $headers, $attachments ): Message {
		$bodyMime = new MimeMessage();
		$bodyHtml = new Part($body);
		$bodyHtml->setEncoding(Mime::ENCODING_QUOTEDPRINTABLE);
		$bodyHtml->setType(Mime::TYPE_HTML);
		$bodyHtml->setCharset("UTF-8");
		$bodyMime->addPart($bodyHtml);
		$bodyMime = $this->addAttachments($bodyMime, $attachments );
		$message->setBody($bodyMime);
		if(empty($headers)){
			$headers = array();
		}
		if(!is_array($headers)){
			$headers = array($headers);
		}
		$message->getHeaders()->addHeaders( $headers );
		$message->setSubject( $subject );
		$message->addTo( $to );
		return $message;
	}

	private function addAttachments(MimeMessage $body, array $attachments){
		foreach($attachments as $attachment){
			$attachmentPart = new Part(fopen($attachment, 'r'));
			$attachmentPart->type        = wp_check_filetype($attachment)['type'];
			$attachmentPart->setFileName(basename($attachment));
			$attachmentPart->setDisposition(Mime::DISPOSITION_ATTACHMENT);
			$attachmentPart->setEncoding(Mime::ENCODING_BASE64);
			$body->addPart($attachmentPart);
		}
		return $body;
	}

	/**
	 * 		 * Trace the parameters to aid in debugging
	 * 		 *
	 *
	 * @param mixed $to
	 * @param mixed $subject
	 * @param mixed $headers
	 * @param mixed $attachments
	 */
	private function traceParameters( $to, $subject, $message, $headers, $attachments ): void {
		$this->logger->trace( 'to:' );
		$this->logger->trace( $to );
		$this->logger->trace( 'subject:' );
		$this->logger->trace( $subject );
		$this->logger->trace( 'headers:' );
		$this->logger->trace( $headers );
		$this->logger->trace( 'attachments:' );
		$this->logger->trace( $attachments );
		$this->logger->trace( 'message:' );
		$this->logger->trace( $message );
	}
}
