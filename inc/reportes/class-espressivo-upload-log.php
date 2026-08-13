<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Espressivo_Upload_Log {
    public const TABLE_SUFFIX = 'ssivo_upload_log';

    public static function table_name(): string {
        global $wpdb;

        return $wpdb->prefix . self::TABLE_SUFFIX;
    }

    /**
     * Tipos creados actualmente por el repositorio Espressivo.
     * El filtro permite agregar otros CPT sin modificar el plugin.
     *
     * @return string[]
     */
    public static function reportable_post_types(): array {
        $types = apply_filters(
            'Espressivo_report_post_types',
            array(
                'post',
                'cartel',
                'clasificado',
                'pro_ad_banner',
                'pro_cat_banner',
            )
        );

        $types = array_values(
            array_unique(
                array_filter(
                    array_map( 'sanitize_key', (array) $types )
                )
            )
        );

        return $types;
    }

    public static function install(): void {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table_name      = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            post_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            post_type varchar(32) NOT NULL,
            uploaded_at datetime NOT NULL,
            PRIMARY KEY  (post_id),
            KEY user_uploaded_at (user_id, uploaded_at),
            KEY post_type (post_type)
        ) {$charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Registra publicaciones históricas usando post_author y post_date como
     * aproximación del cargador y fecha originales.
     */
    public static function backfill_existing_content(): void {
        global $wpdb;

        $types = self::reportable_post_types();
        if ( empty( $types ) ) {
            return;
        }

        $placeholders = implode( ', ', array_fill( 0, count( $types ), '%s' ) );
        $table_name   = self::table_name();

        $sql = "INSERT IGNORE INTO {$table_name} (post_id, user_id, post_type, uploaded_at)
            SELECT ID, post_author, post_type, post_date
            FROM {$wpdb->posts}
            WHERE post_type IN ({$placeholders})
              AND post_author > 0
              AND post_status NOT IN ('auto-draft', 'inherit', 'trash')";

        $wpdb->query( $wpdb->prepare( $sql, ...$types ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * Conserva el primer usuario que creó el contenido. INSERT IGNORE impide
     * que cambios posteriores de autor reescriban el registro original.
     */
    public function capture(
        int $post_id,
        WP_Post $post,
        bool $update,
        ?WP_Post $post_before
    ): void {
        unset( $update, $post_before );

        if (
            $post_id <= 0
            || wp_is_post_revision( $post_id )
            || wp_is_post_autosave( $post_id )
            || in_array( $post->post_status, array( 'auto-draft', 'inherit', 'trash' ), true )
            || ! in_array( $post->post_type, self::reportable_post_types(), true )
        ) {
            return;
        }

        $current_user_id = get_current_user_id();
        $uploader_id     = $current_user_id > 0 ? $current_user_id : (int) $post->post_author;

        if ( $uploader_id <= 0 ) {
            return;
        }

        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                'INSERT IGNORE INTO ' . self::table_name() . ' (post_id, user_id, post_type, uploaded_at) VALUES (%d, %d, %s, %s)',
                $post_id,
                $uploader_id,
                $post->post_type,
                current_time( 'mysql' )
            )
        ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
    }

    public function delete_for_post( int $post_id ): void {
        if ( $post_id <= 0 ) {
            return;
        }

        global $wpdb;

        $wpdb->delete(
            self::table_name(),
            array( 'post_id' => $post_id ),
            array( '%d' )
        ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
    }
}
