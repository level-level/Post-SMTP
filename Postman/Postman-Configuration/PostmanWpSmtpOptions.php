<?php

class PostmanWpSmtpOptions extends PostmanAbstractPluginOptions implements PostmanPluginOptions {
    const SLUG = 'wp_smtp'; // god these names are terrible
    const PLUGIN_NAME = 'WP SMTP';
    const MESSAGE_SENDER_EMAIL = 'from';
    const MESSAGE_SENDER_NAME = 'fromname';
    const HOSTNAME = 'host';
    const PORT = 'port';
    const ENCRYPTION_TYPE = 'smtpsecure';
    const AUTHENTICATION_TYPE = 'smtpauth';
    const USERNAME = 'username';
    const PASSWORD = 'password';
    public function __construct() {
        parent::__construct ();
        $this->options = get_option ( 'wp_smtp_options' );
    }
    /**
     * @return string
     */
    public function getPluginSlug() {
        return self::SLUG;
    }
    /**
     * @return string
     */
    public function getPluginName() {
        return self::PLUGIN_NAME;
    }
    public function getMessageSenderEmail() {
        if (isset ( $this->options [self::MESSAGE_SENDER_EMAIL] ))
            return $this->options [self::MESSAGE_SENDER_EMAIL];
    }
    public function getMessageSenderName() {
        if (isset ( $this->options [self::MESSAGE_SENDER_NAME] ))
            return $this->options [self::MESSAGE_SENDER_NAME];
    }
    public function getHostname() {
        if (isset ( $this->options [self::HOSTNAME] ))
            return $this->options [self::HOSTNAME];
    }
    public function getPort() {
        if (isset ( $this->options [self::PORT] ))
            return $this->options [self::PORT];
    }
    public function getUsername() {
        if (isset ( $this->options [self::USERNAME] ))
            return $this->options [self::USERNAME];
    }
    public function getPassword() {
        if (isset ( $this->options [self::PASSWORD] ))
            return $this->options [self::PASSWORD];
    }
    /**
     * @return null|string
     */
    public function getAuthenticationType() {
        if (isset ( $this->options [self::AUTHENTICATION_TYPE] )) {
            switch ($this->options [self::AUTHENTICATION_TYPE]) {
                case 'yes' :
                    return PostmanOptions::AUTHENTICATION_TYPE_PLAIN;
                case 'no' :
                    return PostmanOptions::AUTHENTICATION_TYPE_NONE;
            }
        }
        return null;
    }
    /**
     * @return null|string
     */
    public function getEncryptionType() {
        if (isset ( $this->options [self::ENCRYPTION_TYPE] )) {
            switch ($this->options [self::ENCRYPTION_TYPE]) {
                case 'ssl' :
                    return PostmanOptions::SECURITY_TYPE_SMTPS;
                case 'tls' :
                    return PostmanOptions::SECURITY_TYPE_STARTTLS;
                case '' :
                    return PostmanOptions::SECURITY_TYPE_NONE;
            }
        }
        return null;
    }
}