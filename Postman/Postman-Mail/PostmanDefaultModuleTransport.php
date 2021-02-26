<?php
require_once 'PostmanModuleTransport.php';
if (! class_exists ( 'PostmanSmtpModuleTransport' )) {
	class PostmanDefaultModuleTransport extends PostmanAbstractZendModuleTransport implements PostmanZendModuleTransport {
		const SLUG = 'default';
		private $fromName;
		private $fromEmail;
		
		/**
		 *
		 * @param mixed $rootPluginFilenameAndPath        	
		 */
		public function __construct($rootPluginFilenameAndPath) {
			parent::__construct ( $rootPluginFilenameAndPath );
			$this->init ();
		}
		
		/**
		 * 		 * Copied from WordPress core
		 * 		 * Set the from name and email
		 *
		 * @return void
		 */
		public function init() {
			parent::init();
			// From email and name
			// If we don't have a name from the input headers
			$this->fromName = apply_filters( 'wp_mail_from_name', 'WordPress' );
			
			/*
			 * If we don't have an email from the input headers default to wordpress@$sitename
			 * Some hosts will block outgoing mail from this address if it doesn't exist but
			 * there's no easy alternative. Defaulting to admin_email might appear to be another
			 * option but some hosts may refuse to relay mail from an unknown domain. See
			 * https://core.trac.wordpress.org/ticket/5007.
			 */
			
			// Get the site domain and get rid of www.
			$site_url = get_bloginfo( 'url' );
			$sitename = strtolower ( PostmanUtils::getHost( $site_url ) );
			
			$this->fromEmail = apply_filters( 'wp_mail_from', 'wordpress@' . $sitename );
		}
		/**
		 * @return false
		 */
		public function isConfiguredAndReady() {
			return false;
		}
		/**
		 * @return true
		 */
		public function isReadyToSendMail() {
			return true;
		}
		public function getFromEmailAddress() {
			return $this->fromEmail;
		}
		public function getFromName() {
			return $this->fromName;
		}
		public function getEnvelopeFromEmailAddress() {
			return $this->getFromEmailAddress ();
		}
		/**
		 * @return false
		 */
		public function isEmailValidationSupported() {
			return false;
		}
		
		/**
		 * 		 * (non-PHPdoc)
		 * 		 *
		 *
		 * @see PostmanAbstractZendModuleTransport::validateTransportConfiguration()
		 *
		 * @return array
		 *
		 * @psalm-return array<empty, empty>
		 */
		protected function validateTransportConfiguration() {
			return array ();
			// no-op, always valid
		}
		
		/**
		 * 		 * (non-PHPdoc)
		 * 		 *
		 *
		 * @see PostmanModuleTransport::createMailEngine()
		 *
		 * @return PostmanZendMailEngine
		 */
		public function createMailEngine() {
			require_once 'PostmanZendMailEngine.php';
			return new PostmanZendMailEngine ( $this );
		}
		
		/**
		 * 		 * (non-PHPdoc)
		 * 		 *
		 *
		 * @see PostmanZendModuleTransport::createZendMailTransport()
		 *
		 * @return Zend_Mail_Transport_Smtp
		 */
		public function createZendMailTransport($fakeHostname, $fakeConfig) {
			$config = array (
					'port' => $this->getPort () 
			);
			return new Zend_Mail_Transport_Smtp ( $this->getHostname (), $config );
		}
		
		/**
		 * 		 * Determines whether Mail Engine locking is needed
		 * 		 *
		 *
		 * @see PostmanModuleTransport::requiresLocking()
		 *
		 * @return bool
		 */
		public function isLockingRequired() {
			return PostmanOptions::AUTHENTICATION_TYPE_OAUTH2 == $this->getAuthenticationType ();
		}
		/**
		 * @return string
		 */
		public function getSlug() {
			return self::SLUG;
		}
		public function getName() {
			return __ ( 'Default', 'post-smtp' );
		}
		/**
		 * @return string
		 */
		public function getHostname() {
			return 'localhost';
		}
		/**
		 * @return int
		 */
		public function getPort() {
			return 25;
		}
		/**
		 * @return string
		 */
		public function getSecurityType() {
			return PostmanOptions::SECURITY_TYPE_NONE;
		}
		/**
		 * @return string
		 */
		public function getAuthenticationType() {
			return PostmanOptions::AUTHENTICATION_TYPE_NONE;
		}
		public function getCredentialsId() {
			$options = PostmanOptions::getInstance ();
			if ($options->isAuthTypeOAuth2 ()) {
				return $options->getClientId ();
			} else {
				return $options->getUsername ();
			}
		}
		public function getCredentialsSecret() {
			$options = PostmanOptions::getInstance ();
			if ($options->isAuthTypeOAuth2 ()) {
				return $options->getClientSecret ();
			} else {
				return $options->getPassword ();
			}
		}
		/**
		 * @return bool
		 */
		public function isServiceProviderGoogle($hostname) {
			return PostmanUtils::endsWith ( $hostname, 'gmail.com' );
		}
		/**
		 * @return bool
		 */
		public function isServiceProviderMicrosoft($hostname) {
			return PostmanUtils::endsWith ( $hostname, 'live.com' );
		}
		/**
		 * @return false|int
		 *
		 * @psalm-return 0|false|positive-int
		 */
		public function isServiceProviderYahoo($hostname) {
			return strpos ( $hostname, 'yahoo' );
		}
		/**
		 * @return false
		 */
		public function isOAuthUsed($authType) {
			return false;
		}
		public final function getConfigurationBid(PostmanWizardSocket $hostData, $userAuthOverride, $originalSmtpServer) {
			return null;
		}
		
		/**
		 * 		 * Does not participate in the Wizard process;
		 * 		 *
		 * 		 * (non-PHPdoc)
		 * 		 *
		 *
		 * @see PostmanModuleTransport::getSocketsForSetupWizardToProbe()
		 *
		 * @return array
		 *
		 * @psalm-return array<empty, empty>
		 */
		public function getSocketsForSetupWizardToProbe($hostname, $smtpServerGuess) {
			return array ();
		}
	}
}
