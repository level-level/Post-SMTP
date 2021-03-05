<?php
if (! class_exists ( "PostmanLogger" )) {
	
	//
	class PostmanLogger {
		const ALL_INT = - 2147483648;
		const TRACE_INT = 5000;
		const DEBUG_INT = 10000;
		const ERROR_INT = 40000;
		const FATAL_INT = 50000;
		const INFO_INT = 20000;
		const OFF_INT = 2147483647;
		const WARN_INT = 30000;
		private $name;
		private $logLevel;
		private $wpDebug;
		function __construct($name) {
			$this->name = $name;
			$this->wpDebug = defined ( 'WP_DEBUG' );
			if (class_exists ( 'PostmanOptions' )) {
				$this->logLevel = PostmanOptions::getInstance ()->getLogLevel ();
			} else {
				$this->logLevel = self::OFF_INT;
			}
		}
		/**
		 * @param false|string $text
		 */
		function trace($text): void {
			$this->printLog ( $text, self::TRACE_INT, 'TRACE' );
		}
		/**
		 * @param string $text
		 */
		function debug($text): void {
			$this->printLog ( $text, self::DEBUG_INT, 'DEBUG' );
		}
		/**
		 * @param string $text
		 */
		function info($text): void {
			$this->printLog ( $text, self::INFO_INT, 'INFO' );
		}
		/**
		 * @param string $text
		 */
		function warn($text): void {
			$this->printLog ( $text, self::WARN_INT, 'WARN' );
		}
		/**
		 * @param string $text
		 */
		function error($text): void {
			$this->printLog ( $text, self::ERROR_INT, 'ERROR' );
		}
		function fatal($text): void {
			$this->printLog ( $text, self::FATAL_INT, 'FATAL' );
		}
		/**
		 * 		 * 		 * better logging thanks to http://www.smashingmagazine.com/2011/03/08/ten-things-every-wordpress-plugin-developer-should-know/
		 * 		 * 		 *
		 * 		 *
		 *
		 * @param mixed $intLogLevel        	
		 * @param mixed $logLevelName
		 * @param false|string $text
		 *
		 * @return void
		 */
		private function printLog($text, $intLogLevel, $logLevelName): void {
			if ($this->wpDebug && $intLogLevel >= $this->logLevel) {
				error_log ( $logLevelName . ' ' . $this->name . ': ' . print_r ( $text, true ) );
			}
		}
		public function isDebug(): bool {
			return self::DEBUG_INT >= $this->logLevel;
		}
		public function isTrace(): bool {
			return self::TRACE_INT >= $this->logLevel;
		}
		public function isInfo(): bool {
			return self::INFO_INT >= $this->logLevel;
		}
	}
}
