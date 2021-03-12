<?php

// Cimy Swift - 9,000
class PostmanCimySwiftSmtpOptions extends PostmanAbstractPluginOptions {
    const SLUG = 'cimy_swift_smtp';
    const PLUGIN_NAME = 'Cimy Swift SMTP';
    const MESSAGE_SENDER_EMAIL = 'sender_mail';
    const MESSAGE_SENDER_NAME = 'sender_name';
    const HOSTNAME = 'server';
    const PORT = 'port';
    const ENCRYPTION_TYPE = 'ssl';
    const USERNAME = 'username';
    const PASSWORD = 'password';
    public function __construct() {
        parent::__construct ();
        $this->options = get_option ( 'cimy_swift_smtp_options' );
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
     * @return string
     */
    public function getAuthenticationType() {
        if (! empty ( $this->options [self::USERNAME] ) && ! empty ( $this->options [self::PASSWORD] )) {
            return PostmanOptions::AUTHENTICATION_TYPE_PLAIN;
        }
        return PostmanOptions::AUTHENTICATION_TYPE_NONE;
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