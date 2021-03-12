<?php
class PostmanEmailLogPurger {
    private $posts;
    private $logger;

    /**
     *
     * @return mixed
     */
    function __construct( $args = array() ) {
        $this->logger = new PostmanLogger( get_class( $this ) );
        $defaults = array(
                'posts_per_page' => -1,
                'offset' => 0,
                'category' => '',
                'category_name' => '',
                'orderby' => 'date',
                'order' => 'DESC',
                'include' => '',
                'exclude' => '',
                'meta_key' => '',
                'meta_value' => '',
                'post_type' => PostmanEmailLogPostType::POSTMAN_CUSTOM_POST_TYPE_SLUG,
                'post_mime_type' => '',
                'post_parent' => '',
                'post_status' => 'private',
                'suppress_filters' => true,
        );
        $args = wp_parse_args( $args, $defaults );
        $query = new WP_Query( $args );
        $this->posts = $query->posts;
    }

    /**
     * @param mixed $postid
     *
     * @return void
     */
    function verifyLogItemExistsAndRemove( $postid ) {
        $force_delete = true;
        foreach ( $this->posts as $post ) {
            if ( $post->ID == $postid ) {
                $this->logger->debug( 'deleting log item ' . (int) $postid );
                wp_delete_post( $postid, $force_delete );
                return;
            }
        }
        $this->logger->warn( 'could not find Postman Log Item #' . $postid );
    }
    function removeAll(): void {
        $this->logger->debug( sprintf( 'deleting %d log items ', count( $this->posts ) ) );
        $force_delete = true;
        foreach ( $this->posts as $post ) {
            wp_delete_post( $post->ID, $force_delete );
        }
    }

    /**
     * 		 *
     *
     * @param mixed $size
     */
    function truncateLogItems( $size ): void {
        $index = count( $this->posts );
        $force_delete = true;
        while ( $index > $size ) {
            $postid = $this->posts [ -- $index ]->ID;
            $this->logger->debug( 'deleting log item ' . $postid );
            wp_delete_post( $postid, $force_delete );
        }
    }
}