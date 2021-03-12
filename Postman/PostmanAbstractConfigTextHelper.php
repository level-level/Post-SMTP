<?php

/**
 *
 * @author jasonhendriks
 */
abstract class PostmanAbstractConfigTextHelper implements PostmanConfigTextHelper {
    /**
     * @return string
     */
    public function getOAuthHelp() {
        $attention = __( 'Attention', 'post-smtp' );
        $errorMessage = sprintf( __('Check this article how to configure Gmail/Gsuite OAuth:<a href="%1$s" target="_blank">Read Here</a>', 'post-smtp' ), 'https://postmansmtp.com/how-to-configure-post-smtp-with-gmailgsuite-using-oauth/' );
        
        return sprintf( '<b style="color:red">%s!</b> %s', $attention, $errorMessage );
    }
    
    function isOauthHost():bool {
        return false;
    }

    function isGoogle():bool {
        return false;
    }
    
    function isMicrosoft():bool {
        return false;
    }
    
    function isYahoo():bool {
        return false;
    }
    /**
     * @return string
     */
    public function getRequestPermissionLinkText() {
        /* translators: where %s is the Email Service Owner (e.g. Google, Microsoft or Yahoo) */
        return sprintf( _x( 'Grant permission with %s', 'Command to initiate OAuth authentication', 'post-smtp' ), $this->getOwnerName() );
    }
}