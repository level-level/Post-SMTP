<?php

class PostmanMicrosoftOAuthScribe extends PostmanAbstractConfigTextHelper {

    public function isMicrosoft():bool {
        return true;
    }
    
    function isOauthHost():bool {
        return true;
    }
    /**
     * @return string
     */
    public function getCallbackUrl() {
        return admin_url( 'options-general.php' );
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
        /* Translators: This description is specific to Microsoft */
        return _x( 'Client ID', 'Name of the OAuth 2.0 Client ID', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getClientSecretLabel() {
        /* Translators: This description is specific to Microsoft */
        return _x( 'Client Secret', 'Name of the OAuth 2.0 Client Secret', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getCallbackUrlLabel() {
        /* Translators: This description is specific to Microsoft */
        return _x( 'Redirect URL', 'Name of the Application Callback URI', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getCallbackDomainLabel() {
        /* Translators: This description is specific to Microsoft */
        return _x( 'Root Domain', 'Name of the Application Callback Domain', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getOwnerName() {
        /* Translators: This description is specific to Microsoft */
        return _x( 'Microsoft', 'Name of the email service owner', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getServiceName() {
        /* Translators: This description is specific to Microsoft */
        return _x( 'Outlook.com', 'Name of the email service', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getApplicationDescription() {
        /* Translators: This description is specific to Microsoft */
        return _x( 'an Application', 'Description of the email service OAuth 2.0 Application', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getApplicationPortalName() {
        /* Translators: This description is specific to Microsoft */
        return _x( 'Microsoft Developer Center', 'Name of the email service portal', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getApplicationPortalUrl() {
        return 'https://account.live.com/developers/applications/index';
    }
    /**
     * @return int
     */
    public function getOAuthPort() {
        return 587;
    }
    /**
     * @return string
     */
    public function getEncryptionType() {
        return PostmanOptions::SECURITY_TYPE_STARTTLS;
    }
}