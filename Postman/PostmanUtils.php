<?php

use Laminas\Validator\EmailAddress;

/**
 *
 * @author jasonhendriks
 */
class PostmanUtils {
	private static $logger;
	private static $emailValidator;

	const POSTMAN_SETTINGS_PAGE_STUB = 'postman';
	const REQUEST_OAUTH2_GRANT_SLUG = 'postman/requestOauthGrant';
	const POSTMAN_EMAIL_LOG_PAGE_STUB = 'postman_email_log';

	// redirections back to THIS SITE should always be relative because of IIS bug
	const POSTMAN_EMAIL_LOG_PAGE_RELATIVE_URL = 'admin.php?page=postman_email_log';
	const POSTMAN_HOME_PAGE_RELATIVE_URL = 'admin.php?page=postman';

	// custom admin post page
	const ADMIN_POST_OAUTH2_GRANT_URL_PART = 'admin-post.php?action=postman/requestOauthGrant';

		const NO_ECHO = false;

	public static function get_logger():PostmanLogger{
		if(empty(self::$logger)){
			self::$logger = new PostmanLogger('PostmanUtils');
		}
		return self::$logger;
	}

	/**
	 *
	 * @param mixed $slug
	 * @return string
	 */
	public static function getPageUrl( $slug ) {
		if ( is_network_admin() ) {
			return network_admin_url( 'admin.php?page=' . $slug );
		}

		return get_admin_url() . 'admin.php?page=' . $slug;
	}

	/**
	 * 	 * Returns an escaped URL
	 */
	public static function getGrantOAuthPermissionUrl(): string {
		return get_admin_url() . self::ADMIN_POST_OAUTH2_GRANT_URL_PART;
	}

	/**
	 * 	 * Returns an escaped URL
	 *
	 * @return string
	 */
	public static function getEmailLogPageUrl() {
		return menu_page_url( self::POSTMAN_EMAIL_LOG_PAGE_STUB, self::NO_ECHO );
	}

	/**
	 * 	 * Returns an escaped URL
	 *
	 * @return string
	 */
	public static function getSettingsPageUrl() {
		return menu_page_url( self::POSTMAN_SETTINGS_PAGE_STUB, self::NO_ECHO );
	}

	public static function isCurrentPagePostmanAdmin( string $page = 'postman' ): bool {
		return isset( $_REQUEST ['page'] ) && substr( $_REQUEST ['page'], 0, strlen( $page ) ) === $page;
	}

	/**
	 * from http://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
	 *
	 * @param mixed $haystack
	 * @param mixed $needle
	 * @return boolean
	 */
	public static function endsWith( $haystack, $needle ) {
		$length = strlen( $needle );
		if ( $length == 0 ) {
			return true;
		}
		return (substr( $haystack, - $length ) === $needle);
	}
	public static function obfuscatePassword( $password ): string {
		return str_repeat( '*', strlen( $password ) );
	}
	/**
	 * Detect if the host is NOT a domain name
	 *
	 * @return bool
	 */
	public static function isHostAddressNotADomainName( string $host ) {
		// IPv4 / IPv6 test from http://stackoverflow.com/a/17871737/4368109
		$ipv6Detected = preg_match( '/(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}\d){0,1}\d)\.){3,3}(25[0-5]|(2[0-4]|1{0,1}\d){0,1}\d)|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}\d){0,1}\d)\.){3,3}(25[0-5]|(2[0-4]|1{0,1}\d){0,1}\d))/', $host );
		$ipv4Detected = preg_match( '/((25[0-5]|(2[0-4]|1{0,1}\d){0,1}\d)\.){3,3}(25[0-5]|(2[0-4]|1{0,1}\d){0,1}\d)/', $host );
		return $ipv4Detected || $ipv6Detected;
		// from http://stackoverflow.com/questions/106179/regular-expression-to-match-dns-hostname-or-ip-address
		// return preg_match ( '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9‌​]{2}|2[0-4][0-9]|25[0-5])$/', $ipAddress );
	}
	/**
	 * 	 * Makes the outgoing HTTP requests
	 * 	 * Inside WordPress we can use wp_remote_post().
	 * 	 * Outside WordPress, not so much.
	 * 	 *
	 *
	 * @param mixed $url
	 * @param (false|mixed|string)[] $parameters
	 *
	 * @return string the HTML body
	 */
	static function remotePostGetBodyOnly( $url, array $parameters, array $headers = array() ) {
		$response = PostmanUtils::remotePost( $url, $parameters, $headers );
		return wp_remote_retrieve_body( $response );
	}

	/**
	 * 	 * Makes the outgoing HTTP requests
	 * 	 * Inside WordPress we can use wp_remote_post().
	 * 	 * Outside WordPress, not so much.
	 * 	 *
	 *
	 * @param mixed $url
	 * @param (false|mixed|string)[] $parameters
	 *
	 * @return array|WP_Error the HTTP response
	 */
	static function remotePost( $url, array $parameters = array(), array $headers = array() ) {
		$args = array(
				'timeout' => PostmanOptions::getInstance()->getConnectionTimeout(),
				'headers' => $headers,
				'body' => $parameters,
		);
		if ( static::get_logger()->isTrace() ) {
			static::get_logger()->trace( sprintf( 'Posting to %s', $url ) );
			static::get_logger()->trace( 'Post header:' );
			static::get_logger()->trace( $headers );
			static::get_logger()->trace( 'Posting args:' );
			static::get_logger()->trace( $parameters );
		}
		$response = wp_remote_post( $url, $args );

		// pre-process the response
		if ( is_wp_error( $response ) ) {
			static::get_logger()->error( $response->get_error_message() );
			throw new Exception( 'Error executing wp_remote_post: ' . $response->get_error_message() );
		} else {
			return $response;
		}
	}
	/**
	 * 	 * A facade function that handles redirects.
	 * 	 * Inside WordPress we can use wp_redirect(). Outside WordPress, not so much. **Load it before postman-core.php**
	 * 	 *
	 *
	 * @param mixed $url
	 */
	static function redirect( $url ): void {
		// redirections back to THIS SITE should always be relative because of IIS bug
		if ( static::get_logger()->isTrace() ) {
			static::get_logger()->trace( sprintf( "Redirecting to '%s'", $url ) );
		}
		wp_redirect( $url );
		exit();
	}
	/**
	 * @return bool|false
	 */
	static function parseBoolean( $var ) {
		return filter_var( $var, FILTER_VALIDATE_BOOLEAN );
	}
	static function logMemoryUse( $startingMemory, $description ): void {
		static::get_logger()->trace( sprintf( $description . ' memory used: %s', size_format( memory_get_usage() - $startingMemory, 2 ) ) );
	}

	/**
	 * From http://stackoverflow.com/a/381275/4368109
	 *
	 * @param mixed $text
	 * @return boolean
	 */
	public static function isEmpty( $text ) {
		// Function for basic field validation (present and neither empty nor only white space
		return ( ! isset( $text ) || trim( $text ) === '');
	}

	/**
	 * 	 * Warning! This can only be called on hook 'init' or later
	 */
	public static function isAdmin(): bool {
		/**
		 * is_admin() will return false when trying to access wp-login.php.
		 * is_admin() will return true when trying to make an ajax request.
		 * is_admin() will return true for calls to load-scripts.php and load-styles.php.
		 * is_admin() is not intended to be used for security checks. It will return true
		 * whenever the current URL is for a page on the admin side of WordPress. It does
		 * not check if the user is logged in, nor if the user even has access to the page
		 * being requested. It is a convenience function for plugins and themes to use for
		 * various purposes, but it is not suitable for validating secured requests.
		 *
		 * Good to know.
		 */
		$logger = PostmanUtils::get_logger();
		if ( $logger->isTrace() ) {
			$logger->trace( 'calling current_user_can' );
		}
		return current_user_can( Postman::MANAGE_POSTMAN_CAPABILITY_NAME ) && is_admin();
	}

	/**
	 * Validate an e-mail address
	 *
	 * @param mixed $email
	 * @return string|bool
	 */
	static function validateEmail( $email ) {
		if ( PostmanOptions::getInstance()->isEmailValidationDisabled() ) {
			return true;
		}
		if ( ! isset( PostmanUtils::$emailValidator ) ) {
			PostmanUtils::$emailValidator = new EmailAddress();
		}
		return PostmanUtils::$emailValidator->isValid( $email );
	}

	/**
	 *
	 * @return string|mixed
	 */
	static function postmanGetServerName() {
		if (! empty( $_SERVER ['SERVER_NAME'] )) {
			$serverName = $_SERVER ['SERVER_NAME'];
		} elseif (! empty( $_SERVER ['HTTP_HOST'] )) {
			$serverName = $_SERVER ['HTTP_HOST'];
		} else {
			$serverName = 'localhost.localdomain';
		}
		return $serverName;
	}

	/**
	 * Does this hostname belong to Google?
	 *
	 * @param mixed $hostname
	 * @return boolean
	 */
	static function isGoogle( $hostname ) {
		return PostmanUtils::endsWith( $hostname, 'gmail.com' ) || PostmanUtils::endsWith( $hostname, 'googleapis.com' );
	}

	/**
	 * 	 * 	 * 	 *
	 * 	 * 	 *
	 * 	 *
	 *
	 * @param mixed $callbackName
	 * @param PostmanConfigurationController|PostmanConnectivityTestController|PostmanDiagnosticTestController|PostmanSendTestEmailController|PostmanViewController $viewController
	 *
	 */
	public static function registerAdminMenu( $viewController, $callbackName, int $priority = 10 ): void {
		$logger = PostmanUtils::get_logger();
		if ( $logger->isTrace() ) {
			$logger->trace( 'Registering admin menu ' . $callbackName );
		}

		add_action( 'admin_menu', array(
				$viewController,
				$callbackName,
		), $priority );
	}

	/**
	 * 	 * 	 *
	 * 	 *
	 *
	 * @param mixed $actionName
	 * @param mixed $callbackName
	 * @param PostmanGetDiagnosticsViaAjax|PostmanGetHostnameByEmailAjaxController|PostmanManageConfigurationAjaxHandler|PostmanPortTestAjaxController|PostmanSendTestEmailAjaxController $class
	 */
	public static function registerAjaxHandler( $actionName, $class, $callbackName ): void {
		if ( is_admin() ) {
			$fullname = 'wp_ajax_' . $actionName;
			// $this->logger->debug ( 'Registering ' . 'wp_ajax_' . $fullname . ' Ajax handler' );
			add_action( $fullname, array(
					$class,
					$callbackName,
			) );
		}
	}

	/**
	 *
	 * @param mixed $parameterName
	 * @return mixed
	 */
	public static function getRequestParameter( $parameterName ) {
		$logger = PostmanUtils::get_logger();
		if ( isset( $_POST [ $parameterName ] ) ) {
			$value = filter_var( $_POST [ $parameterName ], FILTER_SANITIZE_STRING );
			if ( $logger->isTrace() ) {
				$logger->trace( sprintf( 'Found parameter "%s"', $parameterName ) );
				$logger->trace( $value );
			}
			return $value;
		}
	}

	public static function getServerName(): string {
        $host = 'localhost';

        if (array_key_exists('SERVER_NAME', $_SERVER)) {
            $host = $_SERVER['SERVER_NAME'];
        } elseif (function_exists('gethostname') && gethostname() !== false) {
            $host = gethostname();
        } elseif (php_uname('n') !== false) {
            $host = php_uname('n');
        }

        return str_replace('www.', '', $host );
	}

	public static function getHost( $url ): string {
		$host = parse_url( trim( $url ), PHP_URL_HOST );

		return str_replace('www.', '', $host );
	}
}
