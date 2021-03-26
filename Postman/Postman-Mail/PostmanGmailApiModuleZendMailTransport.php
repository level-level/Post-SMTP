<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage Transport
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

use Laminas\Mail\Message;
use Laminas\Mail\Protocol\Smtp;
use Laminas\Mail\Transport\Exception\DomainException;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\Mime\Mime;

/**
 * SMTP connection object
 *
 * Loads an instance of Zend_Mail_Protocol_Smtp and forwards smtp transactions
 *
 * @category Zend
 * @package Zend_Mail
 * @subpackage Transport
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class PostmanGmailApiModuleZendMailTransport implements TransportInterface {
	const SERVICE_OPTION = 'service';
	const MESSAGE_SENDER_EMAIL_OPTION = 'sender_email';
	private $logger;
	private $message;
	private $transcript;
	
	/**
	 * EOL character string used by transport
	 *
	 * @var string
	 * @access public
	 */
	public $EOL = "\n";
	
	/**
	 * Remote smtp hostname or i.p.
	 *
	 * @var string
	 */
	protected $_host;
	
	/**
	 * Port number
	 *
	 * @var integer|null
	 */
	protected $_port;
	
	/**
	 * Local client hostname or i.p.
	 *
	 * @var string
	 */
	protected $_name = 'localhost';
	
	/**
	 * Authentication type OPTIONAL
	 *
	 * @var string
	 */
	protected $_auth;
	
	/**
	 * Config options for authentication
	 *
	 * @var array
	 */
	protected $_config;
	
	/**
	 * @var Smtp
	 */
	protected $_connection;
	
	/**
	 * Constructor.
	 *
	 * @param string $host
	 *        	OPTIONAL (Default: 127.0.0.1)
	 *        	OPTIONAL (Default: null)
	 * @return void
	 * @todo Someone please make this compatible
	 *       with the SendMail transport class.
	 */
	public function __construct($host = '127.0.0.1', Array $config = array()) {
		if (isset ( $config ['name'] )) {
			$this->_name = $config ['name'];
		}
		if (isset ( $config ['port'] )) {
			$this->_port = $config ['port'];
		}
		if (isset ( $config ['auth'] )) {
			$this->_auth = $config ['auth'];
		}
		
		$this->_host = $host;
		$this->_config = $config;
		$this->logger = new PostmanLogger ( get_class ( $this ) );
	}
	
	/**
	 * Class destructor to ensure all open connections are closed
	 *
	 * @return void
	 */
	public function __destruct() {
		if ($this->_connection instanceof Smtp) {
			try {
				$this->_connection->quit ();
			} catch ( Exception $e ) {
				// ignore
			}
			$this->_connection->disconnect ();
		}
	}
	

	
	/**
	 * Gets the connection protocol instance
	 *
	 * @return Smtp|null
	 */
	public function getConnection() {
		return $this->_connection;
	}
	
	/**
	 * Send an email via the Gmail API
	 *
	 * Uses URI https://www.googleapis.com
	 *
	 *
	 * @return void
	 * @todo IMPLEMENT correctly
	 */
	public function send(Message $message)
	{
		throw new BadMethodCallException('Transport not yet implemented.');
	}

	public function getMessage() {
		return $this->message;
	}
}
