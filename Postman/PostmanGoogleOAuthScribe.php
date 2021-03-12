<?php
class PostmanGoogleOAuthScribe extends PostmanAbstractConfigTextHelper {

    public function isGoogle():bool {
        return true;
    }

    function isOauthHost():bool {
        return true;
    }
    /**
     * @return string
     */
    public function getCallbackUrl() {
        // see https://codex.wordpress.org/Function_Reference/admin_url#Related
        return admin_url( 'options-general.php' ) . '?page=postman';
    }
    /**
     * @return string
     */
    function getCallbackDomain() {
        $urlParts = parse_url( $this->getCallbackUrl() );
        if ( isset( $urlParts ['scheme'] ) && isset( $urlParts ['host'] ) ) {
            return $urlParts ['scheme'] . '://' . $urlParts ['host'];
        } else {
            throw new Exception();
        }
    }
    /**
     * @return string
     */
    public function getClientIdLabel() {
        /* Translators: This description is specific to Google */
        return _x( 'Client ID', 'Name of the OAuth 2.0 Client ID', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getClientSecretLabel() {
        /* Translators: This description is specific to Google */
        return _x( 'Client Secret', 'Name of the OAuth 2.0 Client Secret', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getCallbackUrlLabel() {
        /* Translators: This description is specific to Google */
        return _x( 'Authorized redirect URI', 'Name of the Application Callback URI', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getCallbackDomainLabel() {
        /* Translators: This description is specific to Google */
        return _x( 'Authorized JavaScript origins', 'Name of the Application Callback Domain', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getOwnerName() {
        /* Translators: This description is specific to Google */
        return _x( 'Google', 'Name of the email service owner', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getServiceName() {
        /* Translators: This description is specific to Google */
        return _x( 'Gmail', 'Name of the email service', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getApplicationDescription() {
        /* Translators: This description is specific to Google */
        return _x( 'a Client ID for web application', 'Description of the email service OAuth 2.0 Application', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getApplicationPortalName() {
        /* Translators: This description is specific to Google */
        return _x( 'Google Developers Console Gmail Wizard', 'Name of the email service portal', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getApplicationPortalUrl() {
        return 'https://www.google.com/accounts/Logout?continue=https://console.developers.google.com/start/api?id=gmail';
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