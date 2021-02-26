<?php
if (! class_exists ( "PostmanNonOAuthAuthenticationManager" )) {
	
	require_once 'PostmanAuthenticationManager.php';
	class PostmanNonOAuthAuthenticationManager implements PostmanAuthenticationManager {
		
		/**
		 * @return false
		 */
		public function isAccessTokenExpired() {
			return false;
		}
		
		/**
		 * 		 * (non-PHPdoc)
		 * 		 *
		 *
		 * @see PostmanAuthenticationManager::requestVerificationCode()
		 *
		 * @return void
		 */
		public function requestVerificationCode($transactionId) {
			// otherwise known as IllegaStateException
			assert ( false );
		}
		/**
		 * @return void
		 */
		public function processAuthorizationGrantCode($transactionId) {
			// otherwise known as IllegaStateException
			assert ( false );
		}
		/**
		 * @return void
		 */
		public function refreshToken() {
			// no-op
		}
		public function getAuthorizationUrl() {
			return null;
		}
		public function getTokenUrl() {
			return null;
		}
		public function getCallbackUri() {
			return null;
		}
		/**
		 * @return void
		 */
		public function generateRequestTransactionId() {
			// otherwise known as IllegaStateException
			assert ( false );
		}
	}
}
