<?php
interface PostmanOptionsInterface {
    /**
     * I'm stuck with these methods because of Gmail API Extension
     */
    public function save();
    public function isNew();
    public function getLogLevel();
    public function getHostname();
    public function getPort();
    public function getMessageSenderEmail();
    public function getMessageSenderName();
    public function getClientId();
    public function getClientSecret();
    public function getTransportType();
    public function getAuthenticationType();
    public function getEncryptionType();
    public function getUsername();
    public function getPassword();
    public function getReplyTo();
    public function getConnectionTimeout();
    public function getReadTimeout();
    public function isSenderNameOverridePrevented();
    public function isAuthTypePassword();
    public function isAuthTypeOAuth2();
    public function isAuthTypeLogin();
    public function isAuthTypePlain();
    public function isAuthTypeCrammd5();
    public function isAuthTypeNone();

    /**
     *
     * @deprecated
     */
    public function getSenderEmail();
    /**
     *
     * @deprecated
     */
    public function getSenderName();
}