<?php
namespace SSIVO_SEO\Includes;

class Database {
    const TABLE_NAME = 'ssivo_seo_indexable';

    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_NAME;
    }

    public function create_table() {
        global $wpdb;
        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            meta_title varchar(255) DEFAULT '' NOT NULL,
            meta_desc text NOT NULL,
            updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY post_id (post_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    public function get_seo_data( $post_id ) {
        global $wpdb;
        $table_name = self::get_table_name();
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE post_id = %d LIMIT 1", $post_id ), ARRAY_A );
    }

    public function save_seo_data( $post_id, array $data ) {
        global $wpdb;
        return $wpdb->replace(
            self::get_table_name(),
            [
                'post_id'    => $post_id,
                'meta_title' => sanitize_text_field( $data['meta_title'] ),
                'meta_desc'  => sanitize_textarea_field( $data['meta_desc'] ),
                'updated_at' => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%s', '%s' ]
        );
    }
}
