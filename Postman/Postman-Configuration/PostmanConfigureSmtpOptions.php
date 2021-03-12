<?php

// ConfigureSmtp (aka "SMTP") - 80,000
class PostmanConfigureSmtpOptions extends PostmanAbstractPluginOptions {
    const SLUG = 'configure_smtp';
    const PLUGIN_NAME = 'Configure SMTP';
    const MESSAGE_SENDER_EMAIL = 'from_email';
    const MESSAGE_SENDER_NAME = 'from_name';
    const HOSTNAME = 'host';
    const PORT = 'port';
    const AUTHENTICATION_TYPE = 'smtp_auth';
    const ENCRYPTION_TYPE = 'smtp_secure';
    const USERNAME = 'smtp_user';
    const PASSWORD = 'smtp_pass';
    public function __construct() {
        parent::__construct ();
        $this->options = get_option ( 'c2c_configure_smtp' );
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
            if ($this->options [self::AUTHENTICATION_TYPE] == 1) {
                return PostmanOptions::AUTHENTICATION_TYPE_PLAIN;
            } else {
                return PostmanOptions::AUTHENTICATION_TYPE_NONE;
            }
        }
        return null;
    }
    /**
     * @return string
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
        return PostmanOptions::SECURITY_TYPE_NONE;
    }
}