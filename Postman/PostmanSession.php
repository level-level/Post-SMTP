<?php
if (! class_exists ( 'PostmanSession' )) {

	/**
	 * Persist session state to the database
	 *
	 * I heard plugins are forbidden from writing to the http session
	 * on some hosts, such as WPEngine, so this class write session
	 * state to the database instead.
	 *
	 * What's better about this is I don't have to prefix all my
	 * variables with , in fear of colliding with another
	 * plugin's similiarily named variables.
	 *
	 * @author jasonhendriks
	 *
	 */
	class PostmanSession {
		// length of time to keep items around
		const MINUTES_IN_SECONDS = 60;

		//
		const OAUTH_IN_PROGRESS = 'oauth_in_progress';
		const ACTION = 'action';
		const ERROR_MESSAGE = 'error_message';

		// singleton instance
		public static function getInstance() {
			static $inst = null;
			if ($inst === null) {
				$inst = new PostmanSession ();
			}
			return $inst;
		}

		/**
		 * 		 * OAuth is in progress $state is the randomly generated
		 * 		 * transaction ID
		 * 		 *
		 *
		 *
		 * @return bool
		 */
		public function isSetOauthInProgress(): bool {
			return get_transient ( self::OAUTH_IN_PROGRESS ) != false;
		}
		public function setOauthInProgress($state): void {
			set_transient ( self::OAUTH_IN_PROGRESS, $state, 3 * self::MINUTES_IN_SECONDS );
		}
		public function getOauthInProgress() {
			return get_transient ( self::OAUTH_IN_PROGRESS );
		}
		public function unsetOauthInProgress(): void {
			delete_transient ( self::OAUTH_IN_PROGRESS );
		}

		/**
		 * @return bool
		 */
		public function isSetAction(): bool {
			return get_transient ( self::ACTION ) != false;
		}
		public function setAction($action): void {
			set_transient ( self::ACTION, $action, 30 * self::MINUTES_IN_SECONDS );
		}
		public function getAction() {
			return get_transient ( self::ACTION );
		}
		public function unsetAction(): void {
			delete_transient ( self::ACTION );
		}

		/**
		 * @return bool
		 */
		public function isSetErrorMessage(): bool {
			return get_transient ( self::ERROR_MESSAGE ) != false;
		}
		public function setMessage($message): void {
			set_transient ( self::ERROR_MESSAGE, $message, 30 * self::MINUTES_IN_SECONDS );
		}
		public function getMessage() {
			return get_transient ( self::ERROR_MESSAGE );
		}
		public function unsetMessage(): void {
			delete_transient ( self::ERROR_MESSAGE );
		}

	}
}
