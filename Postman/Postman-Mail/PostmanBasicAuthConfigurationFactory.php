<?php

use Laminas\Mail\Transport\SmtpOptions;

class PostmanBasicAuthConfigurationFactory implements PostmanZendMailTransportConfigurationFactory {
    public static function createConfig(PostmanZendModuleTransport $transport): SmtpOptions {
        
        // create Logger
        $logger = new PostmanLogger ( "PostmanBasicAuthConfigurationFactory" );
        
        // retrieve the hostname and port form the transport
        $hostname = $transport->getHostname ();
        $port = $transport->getPort ();
        $securityType = $transport->getSecurityType ();
        $authType = $transport->getAuthenticationType ();
        $username = $transport->getCredentialsId ();
        $password = $transport->getCredentialsSecret ();
        
        $config = array(
            'name' => $hostname,
            'host' => $hostname,
            'port' => $port,
        );

        $connection_config = array();

        $logger->debug ( sprintf ( 'Using %s:%s ', $hostname, $port ) );
        if ($securityType != PostmanOptions::SECURITY_TYPE_NONE) {
            $connection_config['ssl'] = $securityType;
            $logger->debug ( 'Using encryption ' . $securityType );
        } else {
            $logger->debug ( 'Using no encryption' );
        }
        if ($authType != PostmanOptions::AUTHENTICATION_TYPE_NONE) {
            $config['connection_class'] = $authType;
            $connection_config ['username'] = $username;
            $connection_config ['password'] = $password;
            $logger->debug ( sprintf ( 'Using auth %s with username %s and password %s', $authType, $username, PostmanUtils::obfuscatePassword ( $password ) ) );
        } else {
            $logger->debug ( 'Using no authentication' );
        }
        
        if(!empty($connection_config)){
            $config['connection_config'] = $connection_config;
        }

        return new SmtpOptions($config);
    }
}