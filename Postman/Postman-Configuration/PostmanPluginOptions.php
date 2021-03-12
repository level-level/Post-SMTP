<?php
interface PostmanPluginOptions {
    public function getPluginSlug();
    public function getPluginName();
    public function isImportable();
    public function getHostname();
    public function getPort();
    public function getMessageSenderEmail();
    public function getMessageSenderName();
    public function getAuthenticationType();
    public function getEncryptionType();
    public function getUsername();
    public function getPassword();
}