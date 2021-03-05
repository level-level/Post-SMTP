<?php

class PostmanNotify {
    private $notify;

    public function __construct( Postman_Notify $notify ) {
        $this->notify = $notify;
    }

    /**
     * @param string $message
     * @param PostmanEmailLog $log
     */
    public function send( $message, $log ): void {
        $this->notify->send_message( $message );
    }

    /**
     * @return void
     */
    public function push_to_chrome($message) {
        $push_chrome = PostmanOptions::getInstance()->useChromeExtension();

        if ( $push_chrome ) {
            $uid = PostmanOptions::getInstance()->getNotificationChromeUid();

            if ( empty( $uid ) ) {
                return;
            }

            $url = 'https://postmansmtp.com/chrome/' . $uid;

            $args = array(
                'body' => array(
                    'message' => $message
                )
            );

            $response = wp_remote_post( $url , $args );
        }
    }
}