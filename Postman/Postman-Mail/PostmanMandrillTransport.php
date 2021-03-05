<?php
/**
 * Postman Mandrill module
 *
 * @author jasonhendriks
 *        
 */
class PostmanMandrillTransport extends PostmanAbstractModuleTransport implements PostmanModuleTransport {
	const SLUG = 'mandrill_api';
	const PORT = 443;
	const HOST = 'mandrillapp.com';
	const PRIORITY = 9000;
	const MANDRILL_AUTH_OPTIONS = 'postman_mandrill_auth_options';
	const MANDRILL_AUTH_SECTION = 'postman_mandrill_auth_section';
	public function __construct($rootPluginFilenameAndPath) {
		parent::__construct ( $rootPluginFilenameAndPath );
		
		// add a hook on the plugins_loaded event
		add_action ( 'admin_init', function () : void {
			$this->on_admin_init();
		} );
	}
	
	/**
	 *
	 * @param mixed $data        	
	 */
	public function prepareOptionsForExport($data) {
		$data = parent::prepareOptionsForExport ( $data );
		$data [PostmanOptions::MANDRILL_API_KEY] = PostmanOptions::getInstance ()->getMandrillApiKey ();
		return $data;
	}
	/**
	 * @return string
	 */
	public function getProtocol() {
		return 'https';
	}
	
	// this should be standard across all transports
	/**
	 * @return string
	 */
	public function getSlug() {
		return self::SLUG;
	}
	public function getName() {
		return __ ( 'Mandrill API', 'post-smtp' );
	}
	/**
	 * v0.2.1
	 *
	 * @return string
	 */
	public function getHostname() {
		return self::HOST;
	}
	/**
	 * v0.2.1
	 *
	 * @return int
	 */
	public function getPort() {
		return self::PORT;
	}
	/**
	 * v1.7.0
	 *
	 * @return string
	 */
	public function getTransportType() {
		return 'mandrill_api';
	}
	/**
	 * v0.2.1
	 *
	 * @return string
	 */
	public function getAuthenticationType() {
		return '';
	}
	/**
	 * v0.2.1
	 *
	 * @return string
	 */
	public function getSecurityType() {
		return PostmanOptions::SECURITY_TYPE_NONE;
	}
	/**
	 * v0.2.1
	 *
	 * @return string
	 */
	public function getCredentialsId() {
		return $this->options->getClientId ();
	}
	/**
	 * v0.2.1
	 *
	 * @return string
	 */
	public function getCredentialsSecret() {
		return $this->options->getClientSecret ();
	}
	/**
	 * @return false
	 */
	public function isServiceProviderGoogle($hostname) {
		return false;
	}
	/**
	 * @return false
	 */
	public function isServiceProviderMicrosoft($hostname) {
		return false;
	}
	/**
	 * @return false
	 */
	public function isServiceProviderYahoo($hostname) {
		return false;
	}
	/**
	 * @return false
	 */
	public function isOAuthUsed($authType) {
		return false;
	}
	
	/**
	 * 	 * (non-PHPdoc)
	 * 	 *
	 *
	 * @see PostmanModuleTransport::createMailEngine()
	 *
	 * @return PostmanMandrillMailEngine
	 */
	public function createMailEngine() {
		$apiKey = $this->options->getMandrillApiKey ();
		return new PostmanMandrillMailEngine ( $apiKey );
	}
	
	/**
	 * 	 * This short description of the Transport State shows on the Summary screens
	 * 	 * (non-PHPdoc)
	 * 	 *
	 *
	 * @see PostmanModuleTransport::getDeliveryDetails()
	 *
	 * @return string
	 */
	public function getDeliveryDetails() {
		/* translators: where (1) is the secure icon and (2) is the transport name */
		return sprintf ( __ ( 'Postman will send mail via the <b>%1$s %2$s</b>.', 'post-smtp' ), '🔐', $this->getName () );
	}
	
	protected function validateTransportConfiguration() {
		$messages = parent::validateTransportConfiguration ();
		$apiKey = $this->options->getMandrillApiKey ();
		if (empty ( $apiKey )) {
			$messages[] = __ ( 'API Key can not be empty', 'post-smtp' ) . '.';
			$this->setNotConfiguredAndReady ();
		}
		if (! $this->isSenderConfigured ()) {
			$messages[] = __ ( 'Message From Address can not be empty', 'post-smtp' ) . '.';
			$this->setNotConfiguredAndReady ();
		}
		return $messages;
	}
	
	/**
	 * 	 * Mandrill API doesn't care what the hostname or guessed SMTP Server is; it runs it's port test no matter what
	 *
	 * @return array
	 *
	 * @psalm-return array{0: mixed}
	 */
	public function getSocketsForSetupWizardToProbe($hostname, $smtpServerGuess) {
		return array (
				self::createSocketDefinition ( $this->getHostname (), $this->getPort () ) 
		);
	}
	
	/**
	 * 	 * (non-PHPdoc)
	 * 	 *
	 *
	 * @see PostmanModuleTransport::getConfigurationBid()
	 *
	 * @return (int|mixed|null|string)[]
	 *
	 * @psalm-return array{priority: 0|9000, transport: string, hostname: null, label: mixed, message?: string}
	 */
	public function getConfigurationBid(PostmanWizardSocket $hostData, $userAuthOverride, $originalSmtpServer) {
		$recommendation = array ();
		$recommendation ['priority'] = 0;
		$recommendation ['transport'] = self::SLUG;
		$recommendation ['hostname'] = null; // scribe looks this
		$recommendation ['label'] = $this->getName ();
		if ($hostData->hostname == self::HOST && $hostData->port == self::PORT) {
			$recommendation ['priority'] = self::PRIORITY;
			/* translators: where variables are (1) transport name (2) host and (3) port */
			$recommendation ['message'] = sprintf ( __ ( ('Postman recommends the %1$s to host %2$s on port %3$d.') ), $this->getName (), self::HOST, self::PORT );
		}
		return $recommendation;
	}
	
	/**
	 */
	public function createOverrideMenu(PostmanWizardSocket $socket, $winningRecommendation, $userSocketOverride, $userAuthOverride) {
		$overrideItem = parent::createOverrideMenu ( $socket, $winningRecommendation, $userSocketOverride, $userAuthOverride );
		// push the authentication options into the $overrideItem structure
		$overrideItem ['auth_items'] = array (
				array (
						'selected' => true,
						'name' => __ ( 'API Key', 'post-smtp' ),
						'value' => 'api_key' 
				) 
		);
		return $overrideItem;
	}
	
	/**
	 * 	 * Functions to execute on the admin_init event
	 * 	 *
	 * 	 * "Runs at the beginning of every admin page before the page is rendered."
	 * 	 * ref: http://codex.wordpress.org/Plugin_API/Action_Reference#Actions_Run_During_an_Admin_Page_Request
	 *
	 * @return void
	 */
	public function on_admin_init(): void {
		// only administrators should be able to trigger this
		if (PostmanUtils::isAdmin ()) {
			$this->addSettings ();
			$this->registerStylesAndScripts ();
		}
	}
	
	/*
	 * What follows in the code responsible for creating the Admin Settings page
	 */
	
	/**
	 * @return void
	 */
	public function addSettings(): void {
		// the Mandrill Auth section
		add_settings_section ( PostmanMandrillTransport::MANDRILL_AUTH_SECTION, __ ( 'Authentication', 'post-smtp' ), function () : void {
			$this->printMandrillAuthSectionInfo();
		}, PostmanMandrillTransport::MANDRILL_AUTH_OPTIONS );
		
		add_settings_field ( PostmanOptions::MANDRILL_API_KEY, __ ( 'API Key', 'post-smtp' ), function () : void {
			$this->mandrill_api_key_callback();
		}, PostmanMandrillTransport::MANDRILL_AUTH_OPTIONS, PostmanMandrillTransport::MANDRILL_AUTH_SECTION );
	}
	
	/**
	 * @return void
	 */
	public function printMandrillAuthSectionInfo(): void {
		/* Translators: Where (1) is the service URL and (2) is the service name and (3) is a api key URL */
		printf ( '<p id="wizard_mandrill_auth_help">%s</p>', sprintf ( __ ( 'Create an account at <a href="%1$s" target="_blank">%2$s</a> and enter <a href="%3$s" target="_blank">an API key</a> below.', 'post-smtp' ), 'https://mandrillapp.com', 'Mandrillapp.com', 'https://mandrillapp.com/settings' ) );
	}
	
	/**
	 * @return void
	 */
	public function mandrill_api_key_callback(): void {
		printf ( '<input type="password" autocomplete="off" id="mandrill_api_key" name="postman_options[mandrill_api_key]" value="%s" size="60" class="required" placeholder="%s"/>', null !== $this->options->getMandrillApiKey () ? esc_attr ( PostmanUtils::obfuscatePassword ( $this->options->getMandrillApiKey () ) ) : '', __ ( 'Required', 'post-smtp' ) );
		print ' <input type="button" id="toggleMandrillApiKey" value="Show Password" class="button button-secondary" style="visibility:hidden" />';
	}
	
	/**
	 * @return void
	 */
	public function registerStylesAndScripts(): void {
		// register the stylesheet and javascript external resources
		$pluginData = apply_filters ( 'postman_get_plugin_metadata', null );
		wp_register_script ( 'postman_mandrill_script', plugins_url ( 'Postman/Postman-Mail/postman_mandrill.js', $this->rootPluginFilenameAndPath ), array (
				PostmanViewController::JQUERY_SCRIPT,
				PostmanViewController::POSTMAN_SCRIPT 
		), $pluginData ['version'] );
	}
	
	/**
	 * @return void
	 */
	public function enqueueScript() {
		wp_enqueue_script ( 'postman_mandrill_script' );
	}
	
	/**
	 * @return void
	 */
	public function printWizardAuthenticationStep() {
		print '<section class="wizard_mandrill">';
		$this->printMandrillAuthSectionInfo ();
		printf ( '<label for="api_key">%s</label>', __ ( 'API Key', 'post-smtp' ) );
		print '<br />';
		$this->mandrill_api_key_callback ();
		print '</section>';
	}
}
