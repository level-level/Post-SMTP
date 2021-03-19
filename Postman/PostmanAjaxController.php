<?php
	
/**
 *
 * @author jasonhendriks
 */
abstract class PostmanAbstractAjaxHandler {
	protected $logger;
	function __construct() {
		$this->logger = new PostmanLogger ( get_class ( $this ) );
	}

	
	/**
	 *
	 * @param mixed $parameterName        	
	 * @return mixed
	 */
	protected function getBooleanRequestParameter($parameterName) {
		return filter_var ( $this->getRequestParameter ( $parameterName ), FILTER_VALIDATE_BOOLEAN );
	}
	
	/**
	 *
	 * @param mixed $parameterName        	
	 * @return mixed
	 */
	protected function getRequestParameter($parameterName) {
		if (isset ( $_POST [$parameterName] )) {
			$value = $_POST[$parameterName];
			$this->logger->trace ( sprintf ( 'Found parameter "%s"', $parameterName ) );
			$this->logger->trace ( $value );
			return $value;
		}
	}
}
