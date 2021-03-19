<?php

// "WP Mail SMTP" (aka "Email") - 300,000
// each field is a new row in options : mail_from, mail_from_name, smtp_host, smtp_port, smtp_ssl, smtp_auth, smtp_user, smtp_pass
// "Easy SMTP Mail" aka. "Webriti SMTP Mail" appears to share the data format of "WP Mail SMTP" so no need to create an Options class for it.
class PostmanWpMailSmtpOptions extends PostmanAbstractPluginOptions implements PostmanPluginOptions {
	const SLUG = 'wp_mail_smtp';
	const PLUGIN_NAME = 'WP Mail SMTP';
	const MESSAGE_SENDER_EMAIL = 'mail_from';
	const MESSAGE_SENDER_NAME = 'mail_from_name';
	const HOSTNAME = 'smtp_host';
	const PORT = 'smtp_port';
	const ENCRYPTION_TYPE = 'smtp_ssl';
	const AUTHENTICATION_TYPE = 'smtp_auth';
	const USERNAME = 'smtp_user';
	const PASSWORD = 'smtp_pass';
	public function __construct() {
		parent::__construct ();
		$this->options [self::MESSAGE_SENDER_EMAIL] = get_option ( self::MESSAGE_SENDER_EMAIL );
		$this->options [self::MESSAGE_SENDER_NAME] = get_option ( self::MESSAGE_SENDER_NAME );
		$this->options [self::HOSTNAME] = get_option ( self::HOSTNAME );
		$this->options [self::PORT] = get_option ( self::PORT );
		$this->options [self::ENCRYPTION_TYPE] = get_option ( self::ENCRYPTION_TYPE );
		$this->options [self::AUTHENTICATION_TYPE] = get_option ( self::AUTHENTICATION_TYPE );
		$this->options [self::USERNAME] = get_option ( self::USERNAME );
		$this->options [self::PASSWORD] = get_option ( self::PASSWORD );
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
				case 'true' :
					return PostmanOptions::AUTHENTICATION_TYPE_PLAIN;
				case 'false' :
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
				case 'none' :
					return PostmanOptions::SECURITY_TYPE_NONE;
			}
		}
		return null;
	}
}