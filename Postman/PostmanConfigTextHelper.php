<?php
interface PostmanConfigTextHelper {
	public function isOauthHost():bool;
	public function isGoogle():bool;
	public function isMicrosoft():bool;
	public function isYahoo():bool;
	public function getCallbackUrl();
	public function getCallbackDomain();
	public function getClientIdLabel();
	public function getClientSecretLabel();
	public function getCallbackUrlLabel();
	public function getCallbackDomainLabel();
	public function getOwnerName();
	public function getServiceName();
	public function getApplicationDescription();
	public function getApplicationPortalName();
	public function getApplicationPortalUrl();
	public function getOAuthPort();
	public function getEncryptionType();
}
