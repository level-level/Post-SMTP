<?php

/**
 * This class instantiates the Connectors for new users to Postman.
 * It determines which Connectors can supply configuration data
 *
 * @author jasonhendriks
 *        
 */
class PostmanImportableConfiguration {
	private $lazyInit;

	/**
	 * @var PostmanPluginOptions[]
	 */
	private $availableOptions;
	
	private $importAvailable;
	private $logger;
	function __construct() {
		$this->logger = new PostmanLogger ( get_class ( $this ) );
	}
	function init(): void {
		if (! $this->lazyInit) {
			$this->queueIfAvailable ( new PostmanEasyWpSmtpOptions () );
			$this->queueIfAvailable ( new PostmanWpSmtpOptions () );
			$this->queueIfAvailable ( new PostmanWpMailSmtpOptions () );
			$this->queueIfAvailable ( new PostmanCimySwiftSmtpOptions () );
			$this->queueIfAvailable ( new PostmanConfigureSmtpOptions () );
		}
		$this->lazyInit = true;
	}
	private function queueIfAvailable(PostmanPluginOptions $options): void {
		$slug = $options->getPluginSlug ();
		if ($options->isImportable ()) {
			$this->availableOptions [$slug] = $options;
			$this->importAvailable = true;
			$this->logger->debug ( $slug . ' is importable' );
		} else {
			$this->logger->debug ( $slug . ' is not importable' );
		}
	}
	/**
	 * @return PostmanPluginOptions[]
	 *
	 * @psalm-return array<array-key, PostmanPluginOptions>
	 */
	public function getAvailableOptions() {
		$this->init ();
		return $this->availableOptions;
	}
	public function isImportAvailable() {
		$this->init ();
		return $this->importAvailable;
	}
}
