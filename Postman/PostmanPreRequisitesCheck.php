<?php

class PostmanPreRequisitesCheck {
	public static function checkIconv(): bool {
		return function_exists ( 'iconv' );
	}
	public static function checkSpl(): bool {
		return function_exists ( 'spl_autoload_register' );
	}
	public static function checkZlibEncode(): bool {
		return extension_loaded ( "zlib" ) && function_exists ( 'gzcompress' ) && function_exists ( 'gzuncompress' );
	}
	public static function checkOpenSsl(): bool {
		// apparently curl can use ssl libraries in the OS, and doesn't need ssl in PHP
		return extension_loaded ( 'openssl' ) || extension_loaded ( 'php_openssl' );
	}
	public static function checkSockets(): bool {
		return extension_loaded ( 'sockets' ) || extension_loaded ( 'php_sockets' );
	}
	/**
	 * @return bool|false
	 */
	public static function checkAllowUrlFopen() {
		return filter_var ( ini_get ( 'allow_url_fopen' ), FILTER_VALIDATE_BOOLEAN );
	}
	public static function checkMcrypt(): bool {
		return function_exists ( 'mcrypt_get_iv_size' ) && function_exists ( 'mcrypt_create_iv' ) && function_exists ( 'mcrypt_encrypt' ) && function_exists ( 'mcrypt_decrypt' );
	}
	/**
	 * 		 * Return an array of state:
	 * 		 * [n][name=>x,ready=>true|false,required=true|false]
	 *
	 * @return (bool|mixed|string)[][]
	 *
	 * @psalm-return non-empty-list<array{name: string, ready: mixed, required: bool}>
	 */
	public static function getState(): array {
		$state = array ();
		$state[] = array (
				'name' => 'iconv',
				'ready' => self::checkIconv (),
				'required' => true 
		);
		$state[] = array (
				'name' => 'spl_autoload',
				'ready' => self::checkSpl (),
				'required' => true 
		);
		$state[] = array (
				'name' => 'openssl',
				'ready' => self::checkOpenSsl (),
				'required' => false 
		);
		$state[] = array (
				'name' => 'sockets',
				'ready' => self::checkSockets (),
				'required' => false 
		);
		$state[] = array (
				'name' => 'allow_url_fopen',
				'ready' => self::checkAllowUrlFopen (),
				'required' => false 
		);
		$state[] = array (
				'name' => 'mcrypt',
				'ready' => self::checkMcrypt (),
				'required' => false 
		);
		$state[] = array (
				'name' => 'zlib_encode',
				'ready' => self::checkZlibEncode (),
				'required' => false 
		);
		return $state;
	}
	/**
	 *
	 * @return boolean
	 */
	public static function isReady() {
		$states = self::getState ();
		foreach ( $states as $state ) {
			if ($state ['ready'] == false && $state ['required'] == true) {
				return false;
			}
		}
		
		return true;
	}
}