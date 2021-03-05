<?php
if (! class_exists ( 'PostmanMessageHandler' )) {

	class PostmanMessageHandler {

		// The Session variables that carry messages
		const ERROR_CLASS = 'error';
		const WARNING_CLASS = 'update-nag';
		const SUCCESS_CLASS = 'updated';
		private $logger;

		function __construct() {
			$this->logger = new PostmanLogger ( get_class ( $this ) );

			// we'll let the 'init' functions run first; some of them may end the request
			add_action ( 'admin_notices', function () : void {
				$this->displayAllMessages();
			} );
		}

		/**
		 * 		 *
		 *
		 * @param mixed $message
		 *
		 * @return void
		 */
		public function addError($message): void {
			$this->storeMessage ( $message, 'error' );
		}
		/**
		 * 		 *
		 *
		 * @param mixed $message
		 *
		 * @return void
		 */
		public function addWarning($message): void {
			$this->storeMessage ( $message, 'warning' );
		}
		/**
		 * 		 *
		 *
		 * @param mixed $message
		 *
		 * @return void
		 */
		public function addMessage($message): void {
			$this->storeMessage ( $message, 'notify' );
		}

		/**
		 * 		 * store messages for display later
		 * 		 *
		 *
		 * @param mixed $message
		 * @param mixed $type
		 *
		 * @return void
		 */
		private function storeMessage($message, $type): void {
			$messageArray = array ();
			$oldMessageArray = PostmanSession::getInstance ()->getMessage ();
			if ($oldMessageArray) {
				$messageArray = $oldMessageArray;
			}
			$weGotIt = false;
			foreach ( $messageArray as $storedMessage ) {
				if ($storedMessage ['message'] === $message) {
					$weGotIt = true;
				}
			}
			if (! $weGotIt) {
				$m = array (
						'type' => $type,
						'message' => $message
				);
				$messageArray[] = $m;
				PostmanSession::getInstance ()->setMessage ( $messageArray );
			}
		}
		/**
		 * 		 * Retrieve the messages and show them
		 *
		 * @return void
		 */
		public function displayAllMessages(): void {
			$messageArray = PostmanSession::getInstance ()->getMessage ();
			if ($messageArray) {
				PostmanSession::getInstance ()->unsetMessage ();
				foreach ( $messageArray as $m ) {
					$type = $m ['type'];
					switch ($type) {
						case 'error' :
							$className = self::ERROR_CLASS;
							break;
						case 'warning' :
							$className = self::WARNING_CLASS;
							break;
						default :
							$className = self::SUCCESS_CLASS;
							break;
					}
					$message = $m ['message'];
					$this->printMessage ( $message, $className );
				}
			}
		}

		/**
		 * 		 * putput message
		 * 		 *
		 *
		 * @param mixed $message
		 * @param mixed $className
		 *
		 * @return void
		 */
		public function printMessage($message, $className): void {
			printf ( '<div class="%s"><p>%s</p></div>', $className, $message );
		}
	}
}
