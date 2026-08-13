<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Espressivo_Report_Service {
    /**
     * @return array{start: DateTimeImmutable, end: DateTimeImmutable, label: string, preset: string, days: int}|WP_Error
     */
    public static function resolve_range(
        string $preset,
        string $custom_from = '',
        string $custom_to = ''
    ) {
        $timezone = wp_timezone();
        $today    = new DateTimeImmutable( 'today', $timezone );
        $end      = $today->setTime( 23, 59, 59 );

        switch ( $preset ) {
            case '1d':
                $start = $today->setTime( 0, 0, 0 );
                break;

            case '7d':
                $start = $today->modify( '-6 days' )->setTime( 0, 0, 0 );
                break;

            case '15d':
                $start = $today->modify( '-14 days' )->setTime( 0, 0, 0 );
                break;

            case '30d':
                $start = $today->modify( '-29 days' )->setTime( 0, 0, 0 );
                break;

            case '3m':
                $start = $today->modify( '-3 months' )->setTime( 0, 0, 0 );
                break;

            case '6m':
                $start = $today->modify( '-6 months' )->setTime( 0, 0, 0 );
                break;

            case '1y':
                $start = $today->modify( '-1 year' )->setTime( 0, 0, 0 );
                break;

            case 'custom':
                $start = self::parse_date( $custom_from, $timezone );
                $end   = self::parse_date( $custom_to, $timezone );

                if ( ! $start || ! $end ) {
                    return new WP_Error(
                        'Espressivo_invalid_custom_dates',
                        __( 'Selecciona una fecha inicial y una fecha final válidas.', 'espressivo-reportes' )
                    );
                }

                $start = $start->setTime( 0, 0, 0 );
                $end   = $end->setTime( 23, 59, 59 );
                break;

            default:
                return new WP_Error(
                    'Espressivo_invalid_period',
                    __( 'El período seleccionado no es válido.', 'espressivo-reportes' )
                );
        }

        if ( $start > $end ) {
            return new WP_Error(
                'Espressivo_reversed_dates',
                __( 'La fecha inicial no puede ser posterior a la fecha final.', 'espressivo-reportes' )
            );
        }

        if ( $end > $today->setTime( 23, 59, 59 ) ) {
            return new WP_Error(
                'Espressivo_future_date',
                __( 'La fecha final no puede estar en el futuro.', 'espressivo-reportes' )
            );
        }

        $days     = (int) $start->diff( $end )->format( '%a' ) + 1;
        $max_days = (int) apply_filters( 'Espressivo_report_max_range_days', 366 );

        if ( $max_days > 0 && $days > $max_days ) {
            return new WP_Error(
                'Espressivo_range_too_long',
                sprintf(
                    /* translators: %d: maximum number of days. */
                    __( 'El reporte no puede abarcar más de %d días.', 'espressivo-reportes' ),
                    $max_days
                )
            );
        }

        return array(
            'start'  => $start,
            'end'    => $end,
            'label'  => sprintf(
                /* translators: 1: initial date, 2: final date. */
                __( 'Del %1$s al %2$s', 'espressivo-reportes' ),
                wp_date( 'd/m/Y', $start->getTimestamp(), $timezone ),
                wp_date( 'd/m/Y', $end->getTimestamp(), $timezone )
            ),
            'preset' => $preset,
            'days'   => $days,
        );
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, status_counts: array<string, int>, type_counts: array<string, int>, total: int}|WP_Error
     */
    public static function get_user_items(
        int $user_id,
        DateTimeImmutable $start,
        DateTimeImmutable $end
    ) {
        if ( $user_id <= 0 ) {
            return new WP_Error(
                'Espressivo_invalid_user',
                __( 'No se pudo identificar el perfil conectado.', 'espressivo-reportes' )
            );
        }

        $post_types = array_values(
            array_filter(
                Espressivo_Upload_Log::reportable_post_types(),
                'post_type_exists'
            )
        );

        if ( empty( $post_types ) ) {
            return new WP_Error(
                'Espressivo_no_post_types',
                __( 'No hay tipos de contenido configurados para el reporte.', 'espressivo-reportes' )
            );
        }

        $max_items = (int) apply_filters( 'Espressivo_report_max_items', 4000, $user_id );
        $limit     = $max_items > 0 ? $max_items + 1 : 100000;

        global $wpdb;

        $type_placeholders = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );
        $parameters        = array_merge(
            array(
                $user_id,
                $start->format( 'Y-m-d H:i:s' ),
                $end->format( 'Y-m-d H:i:s' ),
            ),
            $post_types,
            array( $limit )
        );

        $sql = "SELECT l.post_id, l.uploaded_at, p.post_title, p.post_status, p.post_type
            FROM " . Espressivo_Upload_Log::table_name() . " l
            INNER JOIN {$wpdb->posts} p ON p.ID = l.post_id
            WHERE l.user_id = %d
              AND l.uploaded_at >= %s
              AND l.uploaded_at <= %s
              AND p.post_type IN ({$type_placeholders})
              AND p.post_status NOT IN ('trash', 'auto-draft', 'inherit')
            ORDER BY l.uploaded_at DESC, l.post_id DESC
            LIMIT %d";

        $rows = $wpdb->get_results(
            $wpdb->prepare( $sql, ...$parameters )
        ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.PreparedSQL.NotPrepared

        if ( ! is_array( $rows ) ) {
            return new WP_Error(
                'Espressivo_query_failed',
                __( 'No se pudo consultar el historial editorial.', 'espressivo-reportes' )
            );
        }

        if ( $max_items > 0 && count( $rows ) > $max_items ) {
            return new WP_Error(
                'Espressivo_too_many_items',
                sprintf(
                    /* translators: %d: maximum report rows. */
                    __( 'El período contiene más de %d registros. Reduce el rango o activa la generación en segundo plano.', 'espressivo-reportes' ),
                    $max_items
                )
            );
        }

        $timezone      = wp_timezone();
        $items         = array();
        $status_counts = array();
        $type_counts   = array();

        foreach ( $rows as $row ) {
            $post_id       = (int) $row->post_id;
            $status_key    = (string) $row->post_status;
            $post_type     = (string) $row->post_type;
            $status_object = get_post_status_object( $status_key );
            $type_object   = get_post_type_object( $post_type );
            $is_public     = 'publish' === $status_key
                && $type_object
                && $type_object->publicly_queryable;

            $url = '';
            if ( $is_public ) {
                $permalink = get_permalink( $post_id );
                $url       = is_string( $permalink ) ? $permalink : '';
            } elseif ( current_user_can( 'edit_post', $post_id ) ) {
                $edit_link = get_edit_post_link( $post_id, 'raw' );
                $url       = is_string( $edit_link ) ? $edit_link : '';
            }

            $uploaded_timestamp = self::mysql_date_to_timestamp(
                (string) $row->uploaded_at,
                $timezone
            );

            $status_counts[ $status_key ] = ( $status_counts[ $status_key ] ?? 0 ) + 1;
            $type_counts[ $post_type ]    = ( $type_counts[ $post_type ] ?? 0 ) + 1;

            $items[] = array(
                'id'           => $post_id,
                'title'        => $row->post_title ?: __( '(Sin título)', 'espressivo-reportes' ),
                'uploaded_at'  => wp_date( 'd/m/Y H:i', $uploaded_timestamp, $timezone ),
                'url'          => $url,
                'display_url'  => self::shorten_url( $url ),
                'link_label'   => $is_public
                    ? __( 'Abrir publicación', 'espressivo-reportes' )
                    : __( 'Abrir en el editor', 'espressivo-reportes' ),
                'status'       => $status_object
                    ? (string) $status_object->label
                    : ucfirst( $status_key ),
                'status_key'   => $status_key,
                'content_type' => $type_object
                    ? (string) $type_object->labels->singular_name
                    : $post_type,
                'post_type'    => $post_type,
            );
        }

        return array(
            'items'         => $items,
            'status_counts' => $status_counts,
            'type_counts'   => $type_counts,
            'total'         => count( $items ),
        );
    }

    private static function parse_date(
        string $value,
        DateTimeZone $timezone
    ): ?DateTimeImmutable {
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
            return null;
        }

        $date   = DateTimeImmutable::createFromFormat( '!Y-m-d', $value, $timezone );
        $errors = DateTimeImmutable::getLastErrors();

        if ( false === $date ) {
            return null;
        }

        if ( is_array( $errors ) && ( $errors['warning_count'] > 0 || $errors['error_count'] > 0 ) ) {
            return null;
        }

        return $date;
    }

    private static function mysql_date_to_timestamp(
        string $mysql_date,
        DateTimeZone $timezone
    ): int {
        $date = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $mysql_date,
            $timezone
        );

        return $date ? $date->getTimestamp() : time();
    }

    private static function shorten_url( string $url ): string {
        if ( '' === $url ) {
            return __( 'Sin enlace disponible', 'espressivo-reportes' );
        }

        $display = preg_replace( '#^https?://#i', '', $url );
        $display = is_string( $display ) ? $display : $url;

        if ( mb_strlen( $display ) <= 72 ) {
            return $display;
        }

        return mb_substr( $display, 0, 69 ) . '...';
    }
}
