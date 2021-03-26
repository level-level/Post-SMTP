<?php

use Laminas\Mail\Transport\TransportInterface;

/**
 *
 * @author jasonhendriks
 *        
 */
abstract class PostmanAbstractModuleTransport implements PostmanModuleTransport {
	private $configurationMessages;
	private $configuredAndReady;
	
	/**
	 * These internal variables are exposed for the subclasses to use
	 *
	 * @var mixed
	 */
	protected $logger;
	protected $options;
	protected $rootPluginFilenameAndPath;
	protected $scribe;
	
	/**
	 */
	public function __construct($rootPluginFilenameAndPath) {
		$this->logger = new PostmanLogger ( get_class ( $this ) );
		$this->options = PostmanOptions::getInstance ();
		$this->rootPluginFilenameAndPath = $rootPluginFilenameAndPath;
	}
	
	/**
	 * 	 * Initialize the Module
	 * 	 *
	 * 	 * Perform validation and create configuration error messages.
	 * 	 * The module is not in a configured-and-ready state until initialization
	 *
	 * @return void
	 */
	public function init() {
		// create the scribe
		$hostname = $this->getHostname ();
		$this->scribe = $this->createScribe ( $hostname );
		
		// validate the transport and generate error messages
		$this->configurationMessages = $this->validateTransportConfiguration ();
	}
	
	/**
	 * 	 * SendGrid API doesn't care what the hostname or guessed SMTP Server is; it runs it's port test no matter what
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
	 * 	 * 	 * Creates a single socket for the Wizard to test
	 * 	 *
	 *
	 * @return (false|mixed|string)[]
	 *
	 * @psalm-return array{host: mixed, port: mixed, id: string, transport_id: mixed, transport_name: mixed, smtp: bool}
	 * @param string $hostname
	 * @param int $port
	 */
	protected function createSocketDefinition($hostname, $port) {
		$socket = array ();
		$socket ['host'] = $hostname;
		$socket ['port'] = $port;
		$socket ['id'] = sprintf ( '%s-%s', $this->getSlug (), $port );
		$socket ['transport_id'] = $this->getSlug ();
		$socket ['transport_name'] = $this->getName ();
		$socket ['smtp'] = false;
		return $socket;
	}
	
	/**
	 *
	 * @param mixed $data        	
	 */
	public function prepareOptionsForExport($data) {
		// no-op
		return $data;
	}
	
	/**
	 * @return void
	 */
	public function printActionMenuItem() {
		printf ( '<li><div class="welcome-icon send_test_email">%s</div></li>', $this->getScribe ()->getRequestPermissionLinkText () );
	}
	
	/**
	 * @return PostmanNonOAuthScribe
	 */
	protected function createScribe($hostname) {
		return new PostmanNonOAuthScribe ( $hostname );
	}
	
	/**
	 * @return void
	 */
	public function enqueueScript() {
		// no-op, this for subclasses
	}
	
	/**
	 * @return array
	 */
	protected function validateTransportConfiguration() {
		$this->configuredAndReady = true;
		return array ();
	}
	
	protected function setNotConfiguredAndReady(): void {
		$this->configuredAndReady = false;
	}
	
	/**
	 * A short-hand way of showing the complete delivery method
	 *
	 * @return string
	 */
	public function getPublicTransportUri() {
		$this->getSlug ();
		$host = $this->getHostname ();
		$port = $this->getPort ();
		$protocol = $this->getProtocol ();
		return sprintf ( '%s://%s:%s', $protocol, $host, $port );
	}
	
	/**
	 * The Message From Address
	 */
	public function getFromEmailAddress() {
		return PostmanOptions::getInstance ()->getMessageSenderEmail ();
	}
	
	/**
	 * The Message From Name
	 */
	public function getFromName() {
		return PostmanOptions::getInstance ()->getMessageSenderName ();
	}
	public function getEnvelopeFromEmailAddress() {
		return PostmanOptions::getInstance ()->getEnvelopeSender ();
	}
	/**
	 * @return bool
	 */
	public function isEmailValidationSupported() {
		return ! PostmanOptions::getInstance ()->isEmailValidationDisabled ();
	}
	
	/**
	 * Make sure the Senders are configured
	 * @return boolean
	 */
	protected function isSenderConfigured() {
		$options = PostmanOptions::getInstance ();
		$messageFrom = $options->getMessageSenderEmail ();
		return ! empty ( $messageFrom );
	}
	
	/**
	 * Get the configuration error messages
	 */
	public function getConfigurationMessages() {
		return $this->configurationMessages;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see PostmanModuleTransport::isConfiguredAndReady()
	 */
	public function isConfiguredAndReady() {
		return $this->configuredAndReady;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see PostmanModuleTransport::isReadyToSendMail()
	 */
	public function isReadyToSendMail() {
		return $this->isConfiguredAndReady ();
	}
	
	/**
	 * 	 * Determines whether Mail Engine locking is needed
	 * 	 *
	 *
	 * @see PostmanModuleTransport::requiresLocking()
	 *
	 * @return false
	 */
	public function isLockingRequired() {
		return false;
	}
	/**
	 * @return bool
	 */
	public function isOAuthUsed($authType) {
		return $authType == PostmanOptions::AUTHENTICATION_TYPE_OAUTH2;
	}
	
	/**
	 * 	 * (non-PHPdoc)
	 * 	 *
	 *
	 * @see PostmanModuleTransport::isWizardSupported()
	 *
	 * @return false
	 */
	public function isWizardSupported() {
		return false;
	}
	
	/**
	 * 	 * This is for step 2 of the Wizard
	 *
	 * @return void
	 */
	public function printWizardMailServerHostnameStep() {
	}
	
	/**
	 * 	 * This is for step 4 of the Wizard
	 *
	 * @return void
	 */
	public function printWizardAuthenticationStep() {
	}
	
	/**
	 *
	 * @return PostmanNonOAuthScribe
	 */
	public function getScribe() {
		return $this->scribe;
	}

	/**
	 * @return array
	 */
	public function populateConfiguration($hostname) {
		return array ();
	}
	/**
	 * 	 *
	 *
	 * @param mixed $winningRecommendation        	
	 *
	 * @return array
	 *
	 * @psalm-return array{message: mixed, transport_type: mixed}
	 */
	public function populateConfigurationFromRecommendation($winningRecommendation) {
		$configuration = array ();
		$configuration ['message'] = $winningRecommendation ['message'];
		$configuration [PostmanOptions::TRANSPORT_TYPE] = $winningRecommendation ['transport'];
		return $configuration;
	}
	
	/**
	 * @return (bool|mixed)[]
	 *
	 * @psalm-return array{secure: mixed, mitm: mixed, hostname_domain_only: mixed, reported_hostname_domain_only: mixed, value: mixed, description: mixed, selected: bool}
	 */
	public function createOverrideMenu(PostmanWizardSocket $socket, $winningRecommendation, $userSocketOverride, $userAuthOverride) {
		$overrideItem = array ();
		$overrideItem ['secure'] = $socket->secure;
		$overrideItem ['mitm'] = $socket->mitm;
		$overrideItem ['hostname_domain_only'] = $socket->hostnameDomainOnly;
		$overrideItem ['reported_hostname_domain_only'] = $socket->reportedHostnameDomainOnly;
		$overrideItem ['value'] = $socket->id;
		$overrideItem ['description'] = $socket->label;
		$overrideItem ['selected'] = ($winningRecommendation ['id'] == $overrideItem ['value']);
		return $overrideItem;
	}
	
	/*
	 * ******************************************************************
	 * Not deprecated, but I wish they didn't live here on the superclass
	 */
	/**
	 * @return bool
	 */
	public function isServiceProviderGoogle($hostname) {
		return PostmanUtils::endsWith ( $hostname, 'gmail.com' ) || PostmanUtils::endsWith ( $hostname, 'googleapis.com' );
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
	
	/*
	 * ********************************
	 * Unused, deprecated methods follow
	 * *********************************
	 */

	abstract public function createMailTransport():TransportInterface;
	
	/**
	 * 	 *
	 *
	 * @deprecated (non-PHPdoc)
	 *
	 * @see PostmanTransport::isTranscriptSupported()
	 *
	 * @return false
	 */
	public function isTranscriptSupported() {
		return false;
	}
	
	/**
	 * 	 * Only here because I can't remove it from the Interface
	 *
	 * @return void
	 */
	public final function getMisconfigurationMessage(PostmanConfigTextHelper $scribe, PostmanOptionsInterface $options, PostmanOAuthToken $token) {
	}
	/**
	 * @return bool
	 */
	public final function isReady(PostmanOptionsInterface $options, PostmanOAuthToken $token) {
		return ! ($this->isConfiguredAndReady ());
	}
	/**
	 * @return bool
	 */
	public final function isConfigured(PostmanOptionsInterface $options, PostmanOAuthToken $token) {
		return ! ($this->isConfiguredAndReady ());
	}
	/**
	 * 	 *
	 *
	 * @deprecated (non-PHPdoc)
	 *
	 * @see PostmanTransport::getConfigurationRecommendation()
	 *
	 * @return void
	 */
	public final function getConfigurationRecommendation($hostData) {
	}
	/**
	 * 	 *
	 *
	 * @deprecated (non-PHPdoc)
	 *
	 * @see PostmanTransport::getHostsToTest()
	 *
	 * @return void
	 */
	public final function getHostsToTest($hostname) {
	}
	protected final function isHostConfigured(PostmanOptions $options): bool {
		$hostname = $options->getHostname ();
		$port = $options->getPort ();
		return !empty ( $hostname ) && !empty ( $port );
	}
	/**
	 * 	 *
	 *
	 * @deprecated (non-PHPdoc)
	 *
	 * @see PostmanTransport::createPostmanMailAuthenticator()
	 *
	 * @return void
	 */
	public final function createPostmanMailAuthenticator(PostmanOptions $options, PostmanOAuthToken $authToken) {
	}
}