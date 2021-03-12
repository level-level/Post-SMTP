<?php

/**
 * Imports Easy WP SMTP options into Postman
 *
 * @author jasonhendriks
 */
class PostmanEasyWpSmtpOptions extends PostmanAbstractPluginOptions implements PostmanPluginOptions {
    const SLUG = 'easy_wp_smtp';
    const PLUGIN_NAME = 'Easy WP SMTP';
    const SMTP_SETTINGS = 'smtp_settings';
    const MESSAGE_SENDER_EMAIL = 'from_email_field';
    const MESSAGE_SENDER_NAME = 'from_name_field';
    const HOSTNAME = 'host';
    const PORT = 'port';
    const ENCRYPTION_TYPE = 'type_encryption';
    const AUTHENTICATION_TYPE = 'autentication';
    const USERNAME = 'username';
    const PASSWORD = 'password';
    public function __construct() {
        parent::__construct ();
        $this->options = get_option ( 'swpsmtp_options' );
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
        if (isset ( $this->options [self::SMTP_SETTINGS] [self::HOSTNAME] ))
            return $this->options [self::SMTP_SETTINGS] [self::HOSTNAME];
    }
    public function getPort() {
        if (isset ( $this->options [self::SMTP_SETTINGS] [self::PORT] ))
            return $this->options [self::SMTP_SETTINGS] [self::PORT];
    }
    public function getUsername() {
        if (isset ( $this->options [self::SMTP_SETTINGS] [self::USERNAME] ))
            return $this->options [self::SMTP_SETTINGS] [self::USERNAME];
    }
    public function getPassword() {
        if (isset ( $this->options [self::SMTP_SETTINGS] [self::PASSWORD] )) {
            // wpecommerce screwed the pooch
            $password = $this->options [self::SMTP_SETTINGS] [self::PASSWORD];
            if (strlen ( $password ) % 4 != 0 || preg_match ( '/[^A-Za-z0-9]/', $password )) {
                $decodedPw = base64_decode ( $password, true );
                $reencodedPw = base64_encode ( $decodedPw );
                if ($reencodedPw === $password) {
                    // encoded
                    return $decodedPw;
                } else {
                    // not encoded
                    return $password;
                }
            }
        }
    }
    /**
     * @return null|string
     */
    public function getAuthenticationType() {
        if (isset ( $this->options [self::SMTP_SETTINGS] [self::AUTHENTICATION_TYPE] )) {
            switch ($this->options [self::SMTP_SETTINGS] [self::AUTHENTICATION_TYPE]) {
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
        if (isset ( $this->options [self::SMTP_SETTINGS] [self::ENCRYPTION_TYPE] )) {
            switch ($this->options [self::SMTP_SETTINGS] [self::ENCRYPTION_TYPE]) {
                case 'ssl' :
                    return PostmanOptions::SECURITY_TYPE_SMTPS;
                case 'tls' :
                    return PostmanOptions::SECURITY_TYPE_STARTTLS;
                case 'none' :
                    return PostmanOptions::SECURITY_TYPE_NONE;
            }
        }
        return null;
    }
}