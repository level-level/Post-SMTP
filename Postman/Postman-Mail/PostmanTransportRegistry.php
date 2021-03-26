<?php

class PostmanTransportRegistry {
	private $transports;
	private $logger;

	/**
	 */
	private function __construct() {
		$this->logger = new PostmanLogger( get_class( $this ) );
	}

	// singleton instance
	public static function getInstance() {
		static $inst = null;
		if ( $inst === null ) {
			$inst = new PostmanTransportRegistry();
		}
		return $inst;
	}
	public function registerTransport( PostmanModuleTransport $instance ): void {
		$this->transports [ $instance->getSlug() ] = $instance;
		$instance->init();
	}
	public function getTransports() {
		return $this->transports;
	}

	/**
	 * Retrieve a Transport by slug
	 * Look up a specific Transport use:
	 * A) when retrieving the transport saved in the database
	 * B) when querying what a theoretical scenario involving this transport is like
	 * (ie.for ajax in config screen)
	 *
	 * @param mixed $slug
	 */
	public function getTransport( $slug ) {
		$transports = $this->getTransports();
		if ( isset( $transports [ $slug ] ) ) {
			return $transports [ $slug ];
		}
	}

	/**
	 * A short-hand way of showing the complete delivery method
	 *
	 * @return string
	 */
	public function getPublicTransportUri( PostmanModuleTransport $transport ) {
		return $transport->getPublicTransportUri();
	}

	/**
	 * Retrieve the transport Postman is currently configured with.
	 *
	 * @return PostmanModuleTransport
	 * @deprecated
	 */
	public function getCurrentTransport() {
		$selectedTransport = PostmanOptions::getInstance()->getTransportType();
		$transports = $this->getTransports();
		if ( ! isset( $transports [ $selectedTransport ] ) ) {
			return $transports ['default'];
		} else {
			return $transports [ $selectedTransport ];
		}
	}

	public function getActiveTransport() {
		$selectedTransport = PostmanOptions::getInstance()->getTransportType();
		$transports = $this->getTransports();
		if ( isset( $transports [ $selectedTransport ] ) ) {
			$transport = $transports [ $selectedTransport ];
			if ( $transport->getSlug() == $selectedTransport && $transport->isConfiguredAndReady() ) {
				return $transport;
			}
		}
		return $transports ['default'];
	}

	/**
	 * Retrieve the transport Postman is currently configured with.
	 *
	 * @return PostmanModuleTransport
	 */
	public function getSelectedTransport() {
		$selectedTransport = PostmanOptions::getInstance()->getTransportType();
		$transports = $this->getTransports();
		if ( isset( $transports [ $selectedTransport ] ) ) {
			return $transports [ $selectedTransport ];
		} else {
			return $transports ['default'];
		}
	}

	/**
	 * 	 * Polls all the installed transports to get a complete list of sockets to probe for connectivity
	 * 	 *
	 *
	 * @param mixed $hostname
	 * @param mixed $smtpServerGuess
	 */
	public function getSocketsForSetupWizardToProbe( $hostname = 'localhost', $smtpServerGuess = null ): array {
		$hosts = array();
		if ( $this->logger->isDebug() ) {
			$this->logger->debug( sprintf( 'Getting sockets for Port Test given hostname %s and smtpServerGuess %s', $hostname, $smtpServerGuess ) );
		}

		$transports = $this->getTransports();
		if ( $hostname !== 'smtp.gmail.com' ) {
			unset( $transports['gmail_api'] );
		}
		foreach ( $transports as $transport ) {
			$socketsToTest = $transport->getSocketsForSetupWizardToProbe( $hostname, $smtpServerGuess );
			if ( $this->logger->isTrace() ) {
				$this->logger->trace( 'sockets to test:' );
				$this->logger->trace( $socketsToTest );
			}
			$hosts = array_merge( $hosts, $socketsToTest );
			if ( $this->logger->isDebug() ) {
				$this->logger->debug( sprintf( 'Transport %s returns %d sockets ', $transport->getName(), count( $socketsToTest ) ) );
			}
		}
		return $hosts;
	}

	/**
	 * If the host port is a possible configuration option, recommend it
	 *
	 * $hostData includes ['host'] and ['port']
	 *
	 * response should include ['success'], ['message'], ['priority']
	 */
	public function getRecommendation( PostmanWizardSocket $hostData, $userAuthOverride, $originalSmtpServer ) {
		$scrubbedUserAuthOverride = $this->scrubUserOverride( $hostData, $userAuthOverride );
		$transport = $this->getTransport( $hostData->transport );
		$recommendation = $transport->getConfigurationBid( $hostData, $scrubbedUserAuthOverride, $originalSmtpServer );
		if ( $this->logger->isDebug() ) {
			$this->logger->debug( sprintf( 'Transport %s bid %s', $transport->getName(), $recommendation ['priority'] ) );
		}
		return $recommendation;
	}

	/**
	 *
	 * @param mixed             $userAuthOverride
	 * @return NULL
	 */
	private function scrubUserOverride( PostmanWizardSocket $hostData, $userAuthOverride ) {
		$this->logger->trace( 'before scrubbing userAuthOverride: ' . $userAuthOverride );

		// validate userAuthOverride
		if ( ! ($userAuthOverride == 'oauth2' || $userAuthOverride == 'password' || $userAuthOverride == 'none') ) {
			$userAuthOverride = null;
		}

		// validate the userAuthOverride
		if ( ! $hostData->auth_xoauth && $userAuthOverride == 'oauth2' ) {
			$userAuthOverride = null;
		}
		if ( ! $hostData->auth_crammd5 && ! $hostData->authPlain && ! $hostData->auth_login && $userAuthOverride == 'password' ) {
			$userAuthOverride = null;
		}
		if ( ! $hostData->auth_none && $userAuthOverride == 'none' ) {
			$userAuthOverride = null;
		}
		$this->logger->trace( 'after scrubbing userAuthOverride: ' . $userAuthOverride );
		return $userAuthOverride;
	}

	/**
	 * @return (bool|mixed)[]
	 *
	 * @psalm-return array{error: bool, message: mixed}
	 */
	public function getReadyMessage(): array {
		if ( $this->getCurrentTransport()->isConfiguredAndReady() ) {
			if ( PostmanOptions::getInstance()->getRunMode() != PostmanOptions::RUN_MODE_PRODUCTION ) {
				return array(
					'error' => true,
					'message' => __( 'Postman is in <em>non-Production</em> mode and is dumping all emails.', 'post-smtp' ),
				);
			} else {
				return array(
					'error' => false,
					'message' => __( 'Postman is configured.', 'post-smtp' ),
				);
			}
		} else {
			return array(
				'error' => true,
				'message' => __( 'Postman is <em>not</em> configured and is mimicking out-of-the-box WordPress email delivery.', 'post-smtp' ),
			);
		}
	}
}
