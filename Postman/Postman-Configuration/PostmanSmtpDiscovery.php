<?php

class PostmanSmtpDiscovery {
	
	// private instance variables
	public $isGoogle;
	public $isGoDaddy;
	public $isWellKnownDomain;
	private $smtpServer;
	private $primaryMx;
	private $email;
	private $domain;
	
	/**
	 * Constructor
	 *
	 * @param mixed $email        	
	 */
	public function __construct($email) {
		$this->email = $email;
		$this->determineSmtpServer ( $email );
		$this->isGoogle = $this->smtpServer == 'smtp.gmail.com';
		$this->isGoDaddy = $this->smtpServer == 'relay-hosting.secureserver.net';
	}
	/**
	 * The SMTP server we suggest to use - this is determined
	 * by looking up the MX hosts for the domain.
	 */
	public function getSmtpServer() {
		return $this->smtpServer;
	}
	private function determineSmtpServer($email): bool {
		$hostname = substr ( strrchr ( $email, "@" ), 1 );
		$this->domain = $hostname;
		$smtp = PostmanSmtpMappings::getSmtpFromEmail ( $hostname );
		if ($smtp) {
			$this->smtpServer = $smtp;
			$this->isWellKnownDomain = true;
			return true;
		} else {
			$host = strtolower ( $this->findMxHostViaDns ( $hostname ) );
			if ($host !== '') {
				$this->primaryMx = $host;
				$smtp = PostmanSmtpMappings::getSmtpFromMx ( $host );
				if ($smtp) {
					$this->smtpServer = $smtp;
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}
	
	/**
	 * Uses getmxrr to retrieve the MX records of a hostname
	 *
	 * @param mixed $hostname        	
	 * @return mixed|boolean
	 */
	private function findMxHostViaDns($hostname) {
		if (function_exists ( 'getmxrr' )) {
			$b_mx_avail = getmxrr ( $hostname, $mx_records, $mx_weight );
		} else {
			$b_mx_avail = $this->getmxrr ( $hostname, $mx_records, $mx_weight );
		}
		if ($b_mx_avail && count ( $mx_records ) > 0) {
			// copy mx records and weight into array $mxs
			$mxs = array ();
			
			foreach ($mx_records as $i => $mx_record) {
				$mxs [$mx_weight [$i]] = $mx_record;
			}
			
			// sort array mxs to get servers with highest prio
			ksort ( $mxs, SORT_NUMERIC );
			reset ( $mxs );
			$mxs_vals = array_values ( $mxs );
			return array_shift ( $mxs_vals );
		} else {
			return false;
		}
	}
	/**
	 * This is a custom implementation of mxrr for Windows PHP installations
	 * which don't have this method natively.
	 *
	 * @param mixed $hostname        	
	 * @param mixed $mxhosts        	
	 * @param mixed $mxweight        	
	 * @return boolean
	 */
	function getmxrr($hostname, &$mxhosts, &$mxweight) {
		if (! is_array ( $mxhosts )) {
			$mxhosts = array ();
		}
		$hostname = escapeshellarg ( $hostname );
		if (! empty ( $hostname )) {
			$output = "";
			@exec ( "nslookup.exe -type=MX $hostname.", $output );
			$imx = - 1;
			
			foreach ( $output as $line ) {
				$imx ++;
				$parts = "";
				if (preg_match ( "/^$hostname\tMX preference = ([0-9]+), mail exchanger = (.*)$/", $line, $parts )) {
					$mxweight [$imx] = $parts [1];
					$mxhosts [$imx] = $parts [2];
				}
			}
			return ($imx != - 1);
		}
		return false;
	}
}
