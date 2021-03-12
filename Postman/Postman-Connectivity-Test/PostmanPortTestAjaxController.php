<?php

/**
 *
 * @author jasonhendriks
 */
class PostmanPortTestAjaxController {
	private $logger;
	/**
	 * Constructor
	 */
	function __construct() {
		$this->logger = new PostmanLogger( get_class( $this ) );
		PostmanUtils::registerAjaxHandler( 'postman_get_hosts_to_test', $this, 'getPortsToTestViaAjax' );
		PostmanUtils::registerAjaxHandler( 'postman_wizard_port_test', $this, 'runSmtpTest' );
		PostmanUtils::registerAjaxHandler( 'postman_wizard_port_test_smtps', $this, 'runSmtpsTest' );
		PostmanUtils::registerAjaxHandler( 'postman_port_quiz_test', $this, 'runPortQuizTest' );
		PostmanUtils::registerAjaxHandler( 'postman_test_port', $this, 'runSmtpTest' );
		PostmanUtils::registerAjaxHandler( 'postman_test_smtps', $this, 'runSmtpsTest' );
	}

	/**
	 * 	 * This Ajax function determines which hosts/ports to test in both the Wizard Connectivity Test and direct Connectivity Test
	 * 	 *
	 * 	 * Given a single outgoing smtp server hostname, return an array of host/port
	 * 	 * combinations to run the connectivity test on
	 */
	function getPortsToTestViaAjax(): void {
		$queryHostname = PostmanUtils::getRequestParameter( 'hostname' );
		// originalSmtpServer is what SmtpDiscovery thinks the SMTP server should be, given an email address
		$originalSmtpServer = PostmanUtils::getRequestParameter( 'original_smtp_server' );
		if ( $this->logger->isDebug() ) {
			$this->logger->debug( 'Probing available transports for sockets against hostname ' . $queryHostname );
		}
		$sockets = PostmanTransportRegistry::getInstance()->getSocketsForSetupWizardToProbe( $queryHostname, $originalSmtpServer );
		$response = array(
				'hosts' => $sockets,
		);
		wp_send_json_success( $response );
	}

	/**
	 * 	 * This Ajax function retrieves whether a TCP port is open or not
	 */
	function runPortQuizTest(): void {
		$hostname = 'portquiz.net';
		$port = (int) PostmanUtils::getRequestParameter( 'port' );
		$this->logger->debug( 'testing TCP port: hostname ' . $hostname . ' port ' . $port );
		$portTest = new PostmanPortTest( $hostname, $port );
		$success = $portTest->genericConnectionTest();
		$this->buildResponse( $hostname, $port, $portTest, $success );
	}

	/**
	 * 	 * This Ajax function retrieves whether a TCP port is open or not.
	 * 	 * This is called by both the Wizard and Port Test
	 */
	function runSmtpTest(): void {
		$hostname = trim( PostmanUtils::getRequestParameter( 'hostname' ) );
		$port = (int) PostmanUtils::getRequestParameter( 'port' );
		$transport = trim( PostmanUtils::getRequestParameter( 'transport' ) );
		$timeout = PostmanUtils::getRequestParameter( 'timeout' );
		$this->logger->trace( $timeout );
		$portTest = new PostmanPortTest( $hostname, $port );
		if ( isset( $timeout ) ) {
			$portTest->setConnectionTimeout( (int) $timeout );
			$portTest->setReadTimeout( (int) $timeout );
		}
		if ( $port != 443 ) {
			$this->logger->debug( sprintf( 'testing SMTP socket %s:%s (%s)', $hostname, $port, $transport ) );
			$success = $portTest->testSmtpPorts();
		} else {
			$this->logger->debug( sprintf( 'testing HTTPS socket %s:%s (%s)', $hostname, $port, $transport ) );
			$success = $portTest->testHttpPorts();
		}
		$this->buildResponse( $hostname, $port, $portTest, $success, $transport );
	}
	/**
	 * 	 * This Ajax function retrieves whether a TCP port is open or not
	 */
	function runSmtpsTest(): void {
		$hostname = trim( PostmanUtils::getRequestParameter( 'hostname' ) );
		$port = (int) PostmanUtils::getRequestParameter( 'port' );
		$transport = trim( PostmanUtils::getRequestParameter( 'transport' ) );
		$this->logger->debug( sprintf( 'testing SMTPS socket %s:%s (%s)', $hostname, $port, $transport ) );
		$portTest = new PostmanPortTest( $hostname, $port );
		$success = $portTest->testSmtpsPorts();
		$this->buildResponse( $hostname, $port, $portTest, $success, $transport );
	}

	/**
	 * 	 *
	 *
	 * @param mixed $hostname
	 * @param mixed $port
	 * @param mixed $success
	 */
	private function buildResponse( $hostname, $port, PostmanPortTest $portTest, $success, string $transport = '' ): void {
		$this->logger->debug( sprintf( 'testing port result for %s:%s success=%s', $hostname, $port, $success ) );
		$response = array(
				'hostname' => $hostname,
				'hostname_domain_only' => $portTest->hostnameDomainOnly,
				'port' => $port,
				'protocol' => $portTest->protocol,
				'secure' => ($portTest->secure),
				'mitm' => ($portTest->mitm),
				'reported_hostname' => $portTest->reportedHostname,
				'reported_hostname_domain_only' => $portTest->reportedHostnameDomainOnly,
				'message' => $portTest->getErrorMessage(),
				'start_tls' => $portTest->startTls,
				'auth_plain' => $portTest->authPlain,
				'auth_login' => $portTest->authLogin,
				'auth_crammd5' => $portTest->authCrammd5,
				'auth_xoauth' => $portTest->authXoauth,
				'auth_none' => $portTest->authNone,
				'try_smtps' => $portTest->trySmtps,
				'success' => $success,
				'transport' => $transport,
		);
		$this->logger->trace( 'Ajax response:' );
		$this->logger->trace( json_encode($response) );
		if ( $success ) {
			wp_send_json_success( $response );
		} else {
			wp_send_json_error( $response );
		}
	}
}
