<?php

/**
 *
 * @author jasonhendriks
 */
class PostmanSendTestEmailAjaxController extends PostmanAbstractAjaxHandler {

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		PostmanUtils::registerAjaxHandler( 'postman_send_test_email', $this, 'sendTestEmailViaAjax' );
	}

	/**
	 * Yes, this procedure is just for testing.
	 *
	 * @return boolean
	 */
	function test_mode() {
		return true;
	}

	/**
	 * 	 * This Ajax sends a test email
	 */
	function sendTestEmailViaAjax(): void {
		// get the email address of the recipient from the HTTP Request
		$email = $this->getRequestParameter( 'email' );

		// get the name of the server from the HTTP Request
		$serverName = PostmanUtils::postmanGetServerName();

		/* translators: where %s is the domain name of the site */
		$subject = sprintf( _x( 'Postman SMTP Test (%s)', 'Test Email Subject', 'post-smtp' ), $serverName );

		// Postman API: indicate to Postman this is just for testing
		add_filter( 'postman_test_email', function () {
			return $this->test_mode();
		} );

		// createt the message content
		$message = $this->createMessageContent();

		// send the message
		$success = wp_mail( $email, $subject, $message );

		// Postman API: remove the testing indicator
		remove_filter( 'postman_test_email', function () {
			return $this->test_mode();
		} );

		// Postman API: retrieve the result of sending this message from Postman
		$result = apply_filters( 'postman_wp_mail_result', null );

		// post-handling
		if ( $success ) {
			$this->logger->debug( 'Test Email delivered to server' );
			// the message was sent successfully, generate an appropriate message for the user
			$statusMessage = sprintf( __( 'Your message was delivered (%d ms) to the SMTP server! Congratulations :)', 'post-smtp' ), $result ['time'] );

						$this->logger->debug( 'statusmessage: ' . $statusMessage );

			// compose the JSON response for the caller
			$response = array(
					'message' => $statusMessage,
					'transcript' => $result ['transcript'],
			);

			// log the response
			if ( $this->logger->isTrace() ) {
				$this->logger->trace( 'Ajax Response:' );
				$this->logger->trace( $response );
			}

			// send the JSON response
			wp_send_json_success( $response );
		} else {
			$this->logger->error( 'Test Email NOT delivered to server - ' . $result ['exception']->getCode() );
			// the message was NOT sent successfully, generate an appropriate message for the user
			$statusMessage = $result ['exception']->getMessage();

			$this->logger->debug( 'statusmessage: ' . $statusMessage );

			// compose the JSON response for the caller
			$response = array(
					'message' => $statusMessage,
					'transcript' => $result ['transcript'],
			);

			// log the response
			if ( $this->logger->isTrace() ) {
				$this->logger->trace( 'Ajax Response:' );
				$this->logger->trace( $response );
			}

			// send the JSON response
			wp_send_json_error( $response );
		}
	}

	/**
	 * Create the multipart message content
	 *
	 * @return string
	 */
	private function createMessageContent() {
		// Postman API: Get the plugin metadata
		$pluginData = apply_filters( 'postman_get_plugin_metadata', null );

		/*
		translators: where %s is the Postman plugin version number (e.g. 1.4) */
		// English - Mandarin - French - Hindi - Spanish - Portuguese - Russian - Japanese
		// http://www.pinyin.info/tools/converter/chars2uninumbers.html
		$greeting = 'Hello! - &#20320;&#22909; - Bonjour! - &#2344;&#2350;&#2360;&#2381;&#2340;&#2375; - ¡Hola! - Ol&#225; - &#1055;&#1088;&#1080;&#1074;&#1077;&#1090;! - &#20170;&#26085;&#12399;';
		$sentBy = sprintf( _x( 'Sent by Postman %s', 'Test Email Tagline', 'post-smtp' ), $pluginData ['version'] );
		$imageSource = __( 'Image source', 'post-smtp' );
		$withPermission = __( 'Used with permission', 'post-smtp' );
		$messageArray = array(
				'Content-Type: text/plain; charset = "UTF-8"',
				'Content-Transfer-Encoding: 8bit',
				'',
				'Hello!',
				'',
				sprintf( '%s - https://wordpress.org/plugins/post-smtp/', $sentBy ),
				'',
				'Content-Type: text/html; charset=UTF-8',
				'Content-Transfer-Encoding: quoted-printable',
				'',
				'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
				'<html xmlns="http://www.w3.org/1999/xhtml">',
				'<head>',
				'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />',
				'<style type="text/css" media="all">',
				'.wporg-notification .im {',
				'	color: #888;',
				'} /* undo a GMail-inserted style */',
				'</style>',
				'</head>',
				'<body class="wporg-notification">',
				'	<div style="background: #e8f6fe; font-family: &amp; quot; Helvetica Neue&amp;quot; , Helvetica ,Arial,sans-serif; font-size: 14px; color: #666; text-align: center; margin: 0; padding: 0">',
				'		<table border="0" cellspacing="0" cellpadding="0" bgcolor="#e8f6fe"	style="background: #e8f6fe; width: 100%;">',
				'			<tbody>',
				'				<tr>',
				'					<td>',
				'						<table border="0" cellspacing="0" cellpadding="0" align="center" style="padding: 0px; width: 100%;"">',
				'							<tbody>',
				'								<tr>',
				'									<td>',
				'										<div style="max-width: 600px; height: 400px; margin: 0 auto; overflow: hidden;background-image:url(\'https://ps.w.org/postman-smtp/assets/email/poofytoo.png\');background-repeat: no-repeat;">',
				sprintf( '											<div style="margin:50px 0 0 300px; width:300px; font-size:2em;">%s</div>', $greeting ),
				sprintf( '											<div style="text-align:right;font-size: 1.4em; color:black;margin:150px 0 0 200px;">%s', $sentBy ),
				'												<br/><span style="font-size: 0.8em"><a style="color:#3f73b9" href="https://wordpress.org/plugins/post-smtp/">https://wordpress.org/plugins/post-smtp/</a></span>',
				'											</div>',
				'										</div>',
				'									</td>',
				'								</tr>',
				'							</tbody>',
				'						</table>',
				sprintf( '						<br><span style="font-size:0.9em;color:#94c0dc;">%s: <a style="color:#94c0dc" href="http://poofytoo.com">poofytoo.com</a> - %s</span>', $imageSource, $withPermission ),
				'					</td>',
				'				</tr>',
				'			</tbody>',
				'		</table>',
				'</body>',
				'</html>',
		);
		return implode( PHP_EOL, $messageArray );
	}
}