<?php
/**
 *
 * @author jasonhendriks
 */
abstract class PostmanAbstractPluginOptions implements PostmanPluginOptions {
    protected $options;
    protected $logger;
    public function __construct() {
        $this->logger = new PostmanLogger ( get_class ( $this ) );
    }
    
    public function isValid(): bool {
        $valid = true;
        $this->getHostname ();
        $port = $this->getPort ();
        $this->getMessageSenderEmail ();
        $this->getMessageSenderName ();
        $auth = $this->getAuthenticationType ();
        $this->getEncryptionType ();
        $this->getUsername ();
        $this->getPassword ();
        $this->logger->trace ( 'host ok ' . $valid );
        ! empty ( $port ) && absint ( $port ) > 0 && absint ( $port ) <= 65535;
        $this->logger->trace ( 'port ok ' . $valid );
        $this->logger->trace ( 'from email ok ' . $valid );
        $this->logger->trace ( 'from name ok ' . $valid );
        $this->logger->trace ( 'auth ok ' . $valid );
        $this->logger->trace ( 'enc ok ' . $valid );
        if ($auth != PostmanOptions::AUTHENTICATION_TYPE_NONE) {
        }
        $this->logger->trace ( 'user/pass ok ' . $valid );
        return (bool) $valid;
    }
    /**
     * @return bool
     */
    public function isImportable() {
        return $this->isValid ();
    }
}