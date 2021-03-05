<?php

class PostmanEmailLogs {

    private $db;

    public $db_name = 'post_smtp_logs';

    private static $fields = array(
        'success',
        'from_header',
        'to_header',
        'cc_header',
        'bcc_header',
        'reply_to_header',
        'transport_uri',
        'original_to',
        'original_subject',
        'original_message',
        'original_headers',
        'session_transcript'
    );

    private static $instance;

    /**
     * @return array[]
     *
     * @psalm-return array<array-key, array{0: mixed}>
     */
    public static function get_data( $post_id ): array {
        $fields = array();
        foreach ( self::$fields as $field ) {
            $fields[$field][0] = get_post_meta( $post_id, $field, true );
        }

        return $fields;
    }

    /**
     * @param array $data
     */
    function save( $data ): void {
        $this->db->query( $this->db->prepare(
            "
		INSERT INTO $this->db_name
		( " . implode( ',', array_keys( $data ) ) . " )
		VALUES ( " . str_repeat( '%s', count( $data ) ) . " )", array_values( $data )
        ) );
    }

}
