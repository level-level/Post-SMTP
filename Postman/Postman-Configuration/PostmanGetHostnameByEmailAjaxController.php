<?php

/**
 *
 * @author jasonhendriks
 */
class PostmanGetHostnameByEmailAjaxController extends PostmanAbstractAjaxHandler {
	const IS_GOOGLE_PARAMETER = 'is_google';
	function __construct() {
		parent::__construct();
		PostmanUtils::registerAjaxHandler( 'postman_check_email', $this, 'getAjaxHostnameByEmail' );
	}
	/**
	 * 	 * This Ajax function retrieves the smtp hostname for a give e-mail address
	 */
	function getAjaxHostnameByEmail(): void {
		$goDaddyHostDetected = $this->getBooleanRequestParameter( 'go_daddy' );
		$email = $this->getRequestParameter( 'email' );
		$d = new PostmanSmtpDiscovery( $email );
		$smtp = $d->getSmtpServer();
		$this->logger->debug( 'given email ' . $email . ', smtp server is ' . $smtp );
		$this->logger->trace( $d );
		if ( $goDaddyHostDetected && ! $d->isGoogle ) {
			// override with the GoDaddy SMTP server
			$smtp = 'relay-hosting.secureserver.net';
			$this->logger->debug( 'detected GoDaddy SMTP server, smtp server is ' . $smtp );
		}
		$response = array(
				'hostname' => $smtp,
				self::IS_GOOGLE_PARAMETER => $d->isGoogle,
				'is_go_daddy' => $d->isGoDaddy,
				'is_well_known' => $d->isWellKnownDomain,
		);
		$this->logger->trace( $response );
		wp_send_json_success( $response );
	}
}