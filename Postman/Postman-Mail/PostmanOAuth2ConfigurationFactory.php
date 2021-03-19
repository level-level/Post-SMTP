<?php

class PostmanOAuth2ConfigurationFactory implements PostmanZendMailTransportConfigurationFactory {
    /**
     * @return array
     *
     * @psalm-return array{ssl: mixed, port: mixed, auth: mixed, xoauth2_request: mixed}
     */
    public static function createConfig(PostmanZendModuleTransport $transport) {
        
        // create Logger
        $logger = new PostmanLogger ( 'PostmanOAuth2ConfigurationFactory' );
        
        // retrieve the hostname and port form the transport
        $hostname = $transport->getHostname ();
        $port = $transport->getPort ();
        
        // the sender email is needed for the OAuth2 Bearer token
        $senderEmail = PostmanOptions::getInstance ()->getEnvelopeSender ();
        assert ( ! empty ( $senderEmail ) );
        
        // the vendor is required for Yahoo's OAuth2 implementation
        $vendor = self::createVendorString ( $hostname );
        
        // create the OAuth2 SMTP Authentication string
        $initClientRequestEncoded = self::createAuthenticationString ( $senderEmail, PostmanOAuthToken::getInstance ()->getAccessToken (), $vendor );
        
        // create the Configuration structure for Zend_Mail
        $config = self::createConfiguration ( $logger, $hostname, $port, $transport->getSecurityType (), $transport->getAuthenticationType (), $initClientRequestEncoded );
        
        // return the Configuration structure
        return $config;
    }
    
    /**
     * 		 *
     * 		 * Create the Configuration structure for Zend_Mail
     * 		 *
     *
     * @param mixed $hostname        	
     * @param mixed $port        	
     * @param mixed $securityType        	
     * @param mixed $authenticationType        	
     * @param mixed $initClientRequestEncoded        	
     *
     * @return array NULL
     *
     * @psalm-return array{ssl: mixed, port: mixed, auth: mixed, xoauth2_request: mixed}
     */
    private static function createConfiguration(PostmanLogger $logger, $hostname, $port, $securityType, $authenticationType, $initClientRequestEncoded): array {
        $config = array (
                'ssl' => $securityType,
                'port' => $port,
                'auth' => $authenticationType,
                'xoauth2_request' => $initClientRequestEncoded 
        );
        $logger->debug ( sprintf ( 'Using auth %s with encryption %s to %s:%s ', $config ['auth'], $config ['ssl'], $hostname, $config ['port'] ) );
        return $config;
    }
    
    /**
     * Create the vendor string (for Yahoo servers only)
     *
     * @param mixed $hostname        	
     * @return string
     */
    private static function createVendorString($hostname) {
        // the vendor is required for Yahoo's OAuth2 implementation
        $vendor = '';
        if (PostmanUtils::endsWith ( $hostname, 'yahoo.com' )) {
            // Yahoo Mail requires a Vendor - see http://imapclient.freshfoo.com/changeset/535%3A80ae438f4e4a/
            $pluginData = apply_filters ( 'postman_get_plugin_metadata', null );
            $vendor = sprintf ( "vendor=Postman SMTP %s\1", $pluginData ['version'] );
        }
        return $vendor;
    }
    
    /**
     * Create the standard OAuth2 SMTP Authentication string
     *
     * @param mixed $senderEmail        	
     * @param mixed $oauth2AccessToken        	
     * @param mixed $vendor        	
     * @return string
     */
    private static function createAuthenticationString($senderEmail, $oauth2AccessToken, $vendor) {
        return base64_encode ( sprintf ( "user=%s\1auth=Bearer %s\1%s\1", $senderEmail, $oauth2AccessToken, $vendor ) );
    }
}