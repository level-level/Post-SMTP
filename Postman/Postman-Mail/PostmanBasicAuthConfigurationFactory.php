<?php

class PostmanBasicAuthConfigurationFactory implements PostmanZendMailTransportConfigurationFactory {
    /**
     * @return array
     *
     * @psalm-return array{port: mixed, ssl?: mixed, auth?: mixed, username?: mixed, password?: mixed}
     */
    public static function createConfig(PostmanZendModuleTransport $transport) {
        
        // create Logger
        $logger = new PostmanLogger ( "PostmanBasicAuthConfigurationFactory" );
        
        // retrieve the hostname and port form the transport
        $hostname = $transport->getHostname ();
        $port = $transport->getPort ();
        $securityType = $transport->getSecurityType ();
        $authType = $transport->getAuthenticationType ();
        $username = $transport->getCredentialsId ();
        $password = $transport->getCredentialsSecret ();
        
        // create the Configuration structure for Zend_Mail
        $config = array (
                'port' => $port 
        );
        $logger->debug ( sprintf ( 'Using %s:%s ', $hostname, $port ) );
        if ($securityType != PostmanOptions::SECURITY_TYPE_NONE) {
            $config ['ssl'] = $securityType;
            $logger->debug ( 'Using encryption ' . $securityType );
        } else {
            $logger->debug ( 'Using no encryption' );
        }
        if ($authType != PostmanOptions::AUTHENTICATION_TYPE_NONE) {
            $config ['auth'] = $authType;
            $config ['username'] = $username;
            $config ['password'] = $password;
            $logger->debug ( sprintf ( 'Using auth %s with username %s and password %s', $authType, $username, PostmanUtils::obfuscatePassword ( $password ) ) );
        } else {
            $logger->debug ( 'Using no authentication' );
        }
        
        // return the Configuration structure
        return $config;
    }
}