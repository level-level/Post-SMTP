<?php
if (! class_exists ( "PostmanAuthenticationManagerFactory" )) {

	class PostmanAuthenticationManagerFactory {
		private $logger;

		// singleton instance
		public static function getInstance() {
			static $inst = null;
			if ($inst === null) {
				$inst = new PostmanAuthenticationManagerFactory ();
			}
			return $inst;
		}
		private function __construct() {
			$this->logger = new PostmanLogger ( get_class ( $this ) );
		}
		public function createAuthenticationManager() {
			$transport = PostmanTransportRegistry::getInstance ()->getSelectedTransport ();
			return $this->createManager ( $transport );
		}
		private function createManager(PostmanModuleTransport $transport) {
			$options = PostmanOptions::getInstance ();
			$authorizationToken = PostmanOAuthToken::getInstance ();
			$hostname = $options->getHostname ();
			$clientId = $options->getClientId ();
			$clientSecret = $options->getClientSecret ();
			$senderEmail = $options->getMessageSenderEmail ();
			$scribe = $transport->getScribe ();
			$redirectUrl = $scribe->getCallbackUrl ();
			if ($transport->isOAuthUsed ( $options->getAuthenticationType () )) {
				if ($transport->isServiceProviderGoogle ( $hostname )) {
					$authenticationManager = new PostmanGoogleAuthenticationManager ( $clientId, $clientSecret, $authorizationToken, $redirectUrl, $senderEmail );
				} else if ($transport->isServiceProviderMicrosoft ( $hostname )) {
					$authenticationManager = new PostmanMicrosoftAuthenticationManager ( $clientId, $clientSecret, $authorizationToken, $redirectUrl );
				} else if ($transport->isServiceProviderYahoo ( $hostname )) {
					$authenticationManager = new PostmanYahooAuthenticationManager ( $clientId, $clientSecret, $authorizationToken, $redirectUrl );
				} else {
					throw new Exception(__('Invalid authentication manager for oAuth.', 'post-smtp'));
				}
			} else {
				$authenticationManager = new PostmanNonOAuthAuthenticationManager ();
			}
			$this->logger->debug ( 'Created ' . get_class ( $authenticationManager ) );
			return $authenticationManager;
		}
	}
}
