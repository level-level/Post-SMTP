<?php

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

	/**
	 * 	 * Initialize the Logger
	 */
	public static function staticInit(): void {
		PostmanUtils::$logger = new PostmanLogger( 'PostmanUtils' );
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
	 * Returns an escaped URL
	 */
	public static function getEmailLogPageUrl() {
		return menu_page_url( self::POSTMAN_EMAIL_LOG_PAGE_STUB, self::NO_ECHO );
	}

	/**
	 * Returns an escaped URL
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
	public static function startsWith( $haystack, $needle ) {
		$length = strlen( $needle );
		return (substr( $haystack, 0, $length ) === $needle);
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
		if ( PostmanUtils::$logger->isTrace() ) {
			PostmanUtils::$logger->trace( sprintf( 'Posting to %s', $url ) );
			PostmanUtils::$logger->trace( 'Post header:' );
			PostmanUtils::$logger->trace( $headers );
			PostmanUtils::$logger->trace( 'Posting args:' );
			PostmanUtils::$logger->trace( $parameters );
		}
		$response = wp_remote_post( $url, $args );

		// pre-process the response
		if ( is_wp_error( $response ) ) {
			PostmanUtils::$logger->error( $response->get_error_message() );
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
		if ( PostmanUtils::$logger->isTrace() ) {
			PostmanUtils::$logger->trace( sprintf( "Redirecting to '%s'", $url ) );
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
		PostmanUtils::$logger->trace( sprintf( $description . ' memory used: %s', PostmanUtils::roundBytes( memory_get_usage() - $startingMemory ) ) );
	}

	/**
	 * Rounds the bytes returned from memory_get_usage to smaller amounts used IEC binary prefixes
	 * See http://en.wikipedia.org/wiki/Binary_prefix
	 *
	 * @param mixed $size
	 * @return string
	 */
	static function roundBytes( $size ) {
		$unit = array(
				'B',
				'KiB',
				'MiB',
				'GiB',
				'TiB',
				'PiB',
		);
		return @round( $size / pow( 1024, ($i = floor( log( $size, 1024 ) )) ), 2 ) . ' ' . $unit [ $i ];
	}

	/**
	 * 	 * Unblock threads waiting on lock()
	 */
	static function unlock(): void {
		if ( PostmanState::getInstance()->isFileLockingEnabled() ) {
			PostmanUtils::deleteLockFile();
		}
	}

	/**
	 * 	 * Processes will block on this method until unlock() is called
	 * 	 * Inspired by http://cubicspot.blogspot.ca/2010/10/forget-flock-and-system-v-semaphores.html
	 * 	 *
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	static function lock() {
		if ( PostmanState::getInstance()->isFileLockingEnabled() ) {
			$attempts = 0;
			while ( true ) {
				// create the semaphore
				$lock = PostmanUtils::createLockFile();
				if ( $lock ) {
					// if we got the lock, return
					return;
				} else {
					$attempts ++;
					if ( $attempts >= 10 ) {
						throw new Exception( sprintf( 'Could not create lockfile %s', '/tmp/.postman.lock' ) );
					}
					sleep( 1 );
				}
			}
		}
	}

	static function lockFileExists(): bool {
		$path = PostmanUtils::calculateTemporaryLockPath( null );

		return file_exists($path);
	}

	static function deleteLockFile( $tempDirectory = null ): bool {
		$path = PostmanUtils::calculateTemporaryLockPath( $tempDirectory );
		$success = @unlink( $path );
		if ( PostmanUtils::$logger->isTrace() ) {
			PostmanUtils::$logger->trace( sprintf( 'Deleting file %s : %s', $path, $success ) );
		}
		return $success;
	}
	/**
	 * @return false|resource
	 */
	static function createLockFile( $tempDirectory = null ) {
		if ( self::lockFileExists() ) {
			self::deleteLockFile();
		}
		$path = PostmanUtils::calculateTemporaryLockPath( $tempDirectory );
		$success = @fopen( $path, 'xb' );
		if ( PostmanUtils::$logger->isTrace() ) {
			PostmanUtils::$logger->trace( sprintf( 'Creating file %s : %s', $path, $success ) );
		}
		return $success;
	}

	/**
	 * Creates the pathname of the lockfile
	 *
	 * @param mixed $tempDirectory
	 * @return string
	 */
	private static function calculateTemporaryLockPath( $tempDirectory ) {
		if ( empty( $tempDirectory ) ) {
			$options = PostmanOptions::getInstance();
			$tempDirectory = $options->getTempDirectory();
		}
		return sprintf( '%s/.postman_%s.lock', $tempDirectory, self::generateUniqueLockKey() );
	}

	/**
	 *
	 * @return string
	 */
	private static function generateUniqueLockKey() {
		// for single sites, use the network_site_url to generate the key because
		// it is unique for every wordpress site unlike the blog ID which may be the same
		$key = hash( 'crc32', network_site_url( '/' ) );
		// TODO for multisites
		// if the subsite is sharing the config - use the network_site_url of site 0
		// if the subsite has its own config - use the network_site_url of the subsite
		return $key;
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
		$logger = PostmanUtils::$logger = new PostmanLogger( 'PostmanUtils' );
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
			PostmanUtils::$emailValidator = new Zend_Validate_EmailAddress();
		}
		return PostmanUtils::$emailValidator->isValid( $email );
	}

	/**
	 * 	 * From http://stackoverflow.com/questions/13430120/str-getcsv-alternative-for-older-php-version-gives-me-an-empty-array-at-the-e
	 * 	 *
	 *
	 * @param mixed $string
	 *
	 * @return (null|string)[]|false|null
	 *
	 * @psalm-return false|non-empty-list<null|string>|null
	 */
	static function postman_strgetcsv_impl( $string ) {
		$fh = fopen( 'php://temp', 'r+' );
		fwrite( $fh, $string );
		rewind( $fh );

		$row = fgetcsv( $fh );

		fclose( $fh );
		return $row;
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
		$logger = PostmanUtils::$logger;
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
	 * @param PostmanGetDiagnosticsViaAjax|PostmanGetHostnameByEmailAjaxController|PostmanImportConfigurationAjaxController|PostmanManageConfigurationAjaxHandler|PostmanPortTestAjaxController|PostmanSendTestEmailAjaxController $class
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
	public static function getBooleanRequestParameter( $parameterName ) {
		return filter_var( self::getRequestParameter( $parameterName ), FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 *
	 * @param mixed $parameterName
	 * @return mixed
	 */
	public static function getRequestParameter( $parameterName ) {
		$logger = PostmanUtils::$logger;
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
