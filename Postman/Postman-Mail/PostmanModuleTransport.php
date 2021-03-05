<?php

interface PostmanModuleTransport extends PostmanTransport {
	const RAW_MESSAGE_FOLLOWS = '

--Raw message follows--

';
	public function getDeliveryDetails();
	public function getSocketsForSetupWizardToProbe($hostname, $smtpServerGuess);
	public function getConfigurationBid(PostmanWizardSocket $hostData, $userAuthOverride, $originalSmtpServer);
	public function isLockingRequired();
	public function createMailEngine();
	public function isWizardSupported();
	public function isConfiguredAndReady();
	public function isReadyToSendMail();
	public function getFromEmailAddress();
	public function getFromName();
	public function getHostname();
	public function getProtocol();
	public function isEmailValidationSupported();
	public function getPort();
	public function init();
	public function getScribe();
	public function getPublicTransportUri();
}