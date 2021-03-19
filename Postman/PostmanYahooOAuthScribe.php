<?php

class PostmanYahooOAuthScribe extends PostmanAbstractConfigTextHelper {
		
    public function isYahoo():bool {
        return true;
    }
    
    function isOauthHost():bool {
        return true;
    }
    /**
     * @return string
     */
    public function getCallbackUrl() {
        return admin_url( 'options-general.php' ) . '?page=postman';
    }
    /**
     * @return string
     */
    function getCallbackDomain() {
        $urlParts = parse_url( $this->getCallbackUrl() );
        if ( isset( $urlParts ['host'] ) ) {
            return $urlParts ['host'];
        } else {
            throw new Exception();
        }
    }
    /**
     * @return string
     */
    public function getClientIdLabel() {
        /* Translators: This description is specific to Yahoo */
        return _x( 'Client ID', 'Name of the OAuth 2.0 Client ID', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getClientSecretLabel() {
        /* Translators: This description is specific to Yahoo */
        return _x( 'Client Secret', 'Name of the OAuth 2.0 Client Secret', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getCallbackUrlLabel() {
        /* Translators: This description is specific to Yahoo */
        return _x( 'Home Page URL', 'Name of the Application Callback URI', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getCallbackDomainLabel() {
        /* Translators: This description is specific to Yahoo */
        return _x( 'Callback Domain', 'Name of the Application Callback Domain', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getOwnerName() {
        /* Translators: This description is specific to Yahoo */
        return _x( 'Yahoo', 'Name of the email service owner', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getServiceName() {
        /* Translators: This description is specific to Yahoo */
        return _x( 'Yahoo Mail', 'Name of the email service', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getApplicationDescription() {
        /* Translators: This description is specific to Yahoo */
        return _x( 'an Application', 'Description of the email service OAuth 2.0 Application', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getApplicationPortalName() {
        /* Translators: This description is specific to Yahoo */
        return _x( 'Yahoo Developer Network', 'Name of the email service portal', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getApplicationPortalUrl() {
        return 'https://developer.yahoo.com/apps/';
    }
    /**
     * @return int
     */
    public function getOAuthPort() {
        return 465;
    }
    /**
     * @return string
     */
    public function getEncryptionType() {
        return PostmanOptions::SECURITY_TYPE_SMTPS;
    }
}