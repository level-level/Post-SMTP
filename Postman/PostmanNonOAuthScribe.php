<?php
class PostmanNonOAuthScribe extends PostmanAbstractConfigTextHelper {
    protected $hostname;
    public function __construct( $hostname ) {
        $this->hostname = $hostname;
    }
    
    public function isGoogle():bool {
        return PostmanUtils::endsWith( $this->hostname, 'gmail.com' );
    }
    
    public function isMicrosoft():bool {
        return PostmanUtils::endsWith( $this->hostname, 'live.com' );
    }
    
    public function isYahoo():bool {
        return PostmanUtils::endsWith( $this->hostname, 'yahoo.com' );
    }
    /**
     * @return string
     */
    public function getOAuthHelp() {
        $text = __( 'Enter an Outgoing Mail Server with OAuth2 capabilities.', 'post-smtp' );
        return sprintf( '<span style="color:red" class="normal">%s</span>', $text );
    }
    /**
     * @return string
     */
    public function getCallbackUrl() {
        return '';
    }
    /**
     * @return string
     */
    function getCallbackDomain() {
        return '';
    }
    /**
     * @return string
     */
    public function getClientIdLabel() {
        return _x( 'Client ID', 'Name of the OAuth 2.0 Client ID', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getClientSecretLabel() {
        return _x( 'Client Secret', 'Name of the OAuth 2.0 Client Secret', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getCallbackUrlLabel() {
        return _x( 'Redirect URI', 'Name of the Application Callback URI', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getCallbackDomainLabel() {
        return _x( 'Website Domain', 'Name of the Application Callback Domain', 'post-smtp' );
    }
    /**
     * @return string
     */
    public function getOwnerName() {
        return '';
    }
    /**
     * @return string
     */
    public function getServiceName() {
        return '';
    }
    /**
     * @return string
     */
    public function getApplicationDescription() {
        return '';
    }
    /**
     * @return string
     */
    public function getApplicationPortalName() {
        return '';
    }
    /**
     * @return string
     */
    public function getApplicationPortalUrl() {
        return '';
    }
    /**
     * @return string
     */
    public function getOAuthPort() {
        return '';
    }
    /**
     * @return string
     */
    public function getEncryptionType() {
        return '';
    }
    public function getRequestPermissionLinkText() {
        return __( 'Grant OAuth 2.0 Permission', 'post-smtp' );
    }
}