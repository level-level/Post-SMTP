<?php
if (! class_exists ( 'PostmanEmailAddress' )) {
	class PostmanEmailAddress {
		private $name;
		private $email;
		public function __construct($email, $name = null) {
			// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
			if (preg_match ( '/(.*)<(.+)>/', $email, $matches )) {
				if (count ( $matches ) == 3) {
					$name = $matches [1];
					$email = $matches [2];
				}
			}
			$this->setEmail ( trim ( $email ) );
			$this->setName ( trim ( $name ) );
		}
		public static function copy(PostmanEmailAddress $orig): self {
			return new PostmanEmailAddress ( $orig->getEmail (), $orig->getName () );
		}
		public function getName() {
			return $this->name;
		}
		public function getEmail() {
			return $this->email;
		}
		public function format(): string {
			$name = $this->getName ();
			if (! empty ( $name )) {
				return sprintf ( '%s <%s>', $this->getName (), $this->getEmail () );
			} else {
				return sprintf ( '%s', $this->getEmail () );
			}
		}
		public function setName(string $name): void {
			$this->name = $name;
		}
		public function setEmail(string $email): void {
			$this->email = $email;
		}
		
		/**
		 * 		 * 		 * Validate the email address
		 * 		 * 		 *
		 * 		 *
		 *
		 * @throws Exception
		 *
		 * @return void
		 *
		 * @param string $desc
		 */
		public function validate($desc = ''): void {
			if (! PostmanUtils::validateEmail ( $this->email )) {
				if (empty ( $desc )) {
					/* Translators: Where %s is the email address */
					$message = sprintf ( 'Invalid e-mail address "%s"', $this->email );
				} else {
					/* Translators: Where (1) is the header name (eg. To) and (2) is the email address */
					$message = sprintf ( 'Invalid "%1$s" e-mail address "%2$s"', $desc, $this->email );
				}
				$logger = new PostmanLogger ( get_class ( $this ) );
				$logger->warn ( $message );
				throw new Exception ( $message );
			}
		}
		
		/**
		 * Accept a String of addresses or an array and return an array
		 *
		 * @param mixed $emails        	
		 *
		 * @return (mixed|string)[]
		 *
		 * @psalm-return array<array-key, mixed|string>
		 */
		public static function convertToArray($emails): array {
			assert ( ! empty ( $emails ) );
			if (! is_array ( $emails )) {
				// http://tiku.io/questions/955963/splitting-comma-separated-email-addresses-in-a-string-with-commas-in-quotes-in-p
				$t = str_getcsv ( $emails );
				$emails = array ();
				foreach ( $t as $k => $v ) {
					if (strpos ( $v, ',' ) !== false) {
						$t [$k] = '"' . str_replace ( ' <', '" <', $v );
					}
					$tokenizedEmail = trim ( $t [$k] );
					array_push ( $emails, $tokenizedEmail );
				}
			}
			return $emails;
		}
		/**
		 * @param string $desc
		 */
		public function log(PostmanLogger $log, $desc): void {
			$message = $desc . ' email=' . $this->getEmail ();
			if (! empty ( $this->name )) {
				$message .= ' name=' . $this->getName ();
			}
			$log->debug ( $message );
		}
	}
}