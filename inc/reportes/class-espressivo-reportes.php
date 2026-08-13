<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Espressivo_Reportes {
    public const CAPABILITY = 'export_own_editorial_report';
    public const EXPORT_ACTION = 'espressivo_export_report_pdf';
    public const NONCE_ACTION  = 'espressivo_export_report_pdf';
    public const NONCE_NAME    = 'espressivo_report_nonce';

    private const MENU_SLUG          = 'espressivo-mis-reportes';
    private const CAP_VERSION_OPTION = 'Espressivo_report_capabilities_version';
    private const DB_VERSION_OPTION  = 'Espressivo_report_database_version';

    public static function boot(): void {
        $plugin     = new self();
        $upload_log = new Espressivo_Upload_Log();

        add_action( 'admin_init', array( $plugin, 'maybe_upgrade' ) );
        add_action( 'admin_menu', array( $plugin, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $plugin, 'enqueue_assets' ) );
        add_action( 'admin_post_' . self::EXPORT_ACTION, array( $plugin, 'handle_export' ) );
        add_filter( 'user_has_cap', array( $plugin, 'deny_subscriber_capability' ), 10, 4 );

        add_action( 'wp_after_insert_post', array( $upload_log, 'capture' ), 20, 4 );
        add_action( 'before_delete_post', array( $upload_log, 'delete_for_post' ) );
    }

    public static function activate(): void {
        Espressivo_Upload_Log::install();
        Espressivo_Upload_Log::backfill_existing_content();
        self::sync_capabilities();

        update_option( self::CAP_VERSION_OPTION, ESPRESSIVO_REPORTES_VERSION, false );
        update_option( self::DB_VERSION_OPTION, ESPRESSIVO_REPORTES_VERSION, false );
    }

    public function maybe_upgrade(): void {
        // Mantiene sincronizados los roles que se creen después de activar el plugin.
        // La función solo escribe en la base de datos cuando falta o sobra la capacidad.
        self::sync_capabilities();

        $cap_version = (string) get_option( self::CAP_VERSION_OPTION, '' );
        if ( ESPRESSIVO_REPORTES_VERSION !== $cap_version ) {
            self::sync_capabilities();
            update_option( self::CAP_VERSION_OPTION, ESPRESSIVO_REPORTES_VERSION, false );
        }

        $db_version = (string) get_option( self::DB_VERSION_OPTION, '' );
        if ( ESPRESSIVO_REPORTES_VERSION !== $db_version ) {
            Espressivo_Upload_Log::install();
            Espressivo_Upload_Log::backfill_existing_content();
            update_option( self::DB_VERSION_OPTION, ESPRESSIVO_REPORTES_VERSION, false );
        }
    }

    /**
     * Excluye expresamente subscriber aunque alguien le agregue la capacidad
     * de manera accidental o individual.
     *
     * @param array<string, bool> $allcaps
     * @param string[]            $caps
     * @param mixed[]             $args
     * @return array<string, bool>
     */
    public function deny_subscriber_capability(
        array $allcaps,
        array $caps,
        array $args,
        WP_User $user
    ): array {
        unset( $caps, $args );

        if ( in_array( 'subscriber', $user->roles, true ) ) {
            $allcaps[ self::CAPABILITY ] = false;
        }

        return $allcaps;
    }

    private static function sync_capabilities(): void {
        $roles = wp_roles();
        if ( ! $roles ) {
            return;
        }

        foreach ( array_keys( $roles->roles ) as $role_slug ) {
            $role = get_role( (string) $role_slug );
            if ( ! $role ) {
                continue;
            }

            if ( 'subscriber' === $role_slug ) {
                if ( $role->has_cap( self::CAPABILITY ) ) {
                    $role->remove_cap( self::CAPABILITY );
                }
                continue;
            }

            if ( ! $role->has_cap( self::CAPABILITY ) ) {
                $role->add_cap( self::CAPABILITY, true );
            }
        }
    }

    public function register_menu(): void {
        add_menu_page(
            __( 'Mis reportes editoriales', 'espressivo-reportes' ),
            __( 'Mis reportes', 'espressivo-reportes' ),
            self::CAPABILITY,
            self::MENU_SLUG,
            array( $this, 'render_admin_page' ),
            'dashicons-media-document',
            61
        );
    }

    public function enqueue_assets( string $hook_suffix ): void {
        if ( 'toplevel_page_' . self::MENU_SLUG !== $hook_suffix ) {
            return;
        }

        wp_enqueue_style(
            'erp-admin',
            get_template_directory_uri() . '/assets/css/admin-reportes.css',
            array(),
            ESPRESSIVO_REPORTES_VERSION
        );

        wp_enqueue_script(
            'erp-admin',
            get_template_directory_uri() . '/assets/js/admin-reportes.js',
            array(),
            ESPRESSIVO_REPORTES_VERSION,
            true
        );
    }

    public function render_admin_page(): void {
        if ( ! current_user_can( self::CAPABILITY ) ) {
            wp_die(
                esc_html__( 'No tienes permiso para exportar reportes.', 'espressivo-reportes' ),
                '',
                array( 'response' => 403 )
            );
        }

        $user             = wp_get_current_user();
        $role_label       = $this->get_primary_role_label( $user );
        $dompdf_available = class_exists( '\\Dompdf\\Dompdf' );
        $reportable_types = $this->get_reportable_type_labels();

        require ESPRESSIVO_REPORTES_DIR . 'templates/admin-page.php';
    }

    public function handle_export(): void {
        if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
            wp_die(
                esc_html__( 'Método de solicitud no permitido.', 'espressivo-reportes' ),
                '',
                array( 'response' => 405 )
            );
        }

        if ( ! current_user_can( self::CAPABILITY ) ) {
            wp_die(
                esc_html__( 'No tienes permiso para exportar reportes.', 'espressivo-reportes' ),
                '',
                array( 'response' => 403 )
            );
        }

        check_admin_referer( self::NONCE_ACTION, self::NONCE_NAME );

        if ( ! class_exists( '\\Dompdf\\Dompdf' ) ) {
            wp_die(
                wp_kses_post(
                    __( 'Dompdf no está instalado. Ejecuta <code>composer install --no-dev --optimize-autoloader</code> dentro de la carpeta del plugin.', 'espressivo-reportes' )
                ),
                esc_html__( 'Generador PDF no disponible', 'espressivo-reportes' ),
                array( 'response' => 500 )
            );
        }

        $preset = isset( $_POST['period'] )
            ? sanitize_key( wp_unslash( $_POST['period'] ) )
            : '7d';

        $custom_from = isset( $_POST['date_from'] )
            ? sanitize_text_field( wp_unslash( $_POST['date_from'] ) )
            : '';

        $custom_to = isset( $_POST['date_to'] )
            ? sanitize_text_field( wp_unslash( $_POST['date_to'] ) )
            : '';

        $range = Espressivo_Report_Service::resolve_range(
            $preset,
            $custom_from,
            $custom_to
        );

        if ( is_wp_error( $range ) ) {
            wp_die(
                esc_html( $range->get_error_message() ),
                esc_html__( 'Período inválido', 'espressivo-reportes' ),
                array( 'response' => 400 )
            );
        }

        $current_user = wp_get_current_user();
        $target_user  = $current_user;

        if ( isset( $_POST['espressivo_user_id'] ) ) {
            $requested_id = (int) $_POST['espressivo_user_id'];
            if ( $requested_id !== (int) $current_user->ID && current_user_can( 'manage_options' ) ) {
                $found_user = get_userdata( $requested_id );
                if ( $found_user ) {
                    $target_user = $found_user;
                }
            }
        }

        $result = Espressivo_Report_Service::get_user_items(
            (int) $target_user->ID,
            $range['start'],
            $range['end']
        );

        if ( is_wp_error( $result ) ) {
            wp_die(
                esc_html( $result->get_error_message() ),
                esc_html__( 'No se pudo generar el reporte', 'espressivo-reportes' ),
                array( 'response' => 500 )
            );
        }

        $generator = new Espressivo_PDF_Generator();
        $generator->download(
            array(
                'user'          => $target_user,
                'role_label'    => $this->get_primary_role_label( $target_user ),
                'range'         => $range,
                'items'         => $result['items'],
                'status_counts' => $result['status_counts'],
                'type_counts'   => $result['type_counts'],
                'total'         => $result['total'],
            )
        );
    }

    private function get_primary_role_label( WP_User $user ): string {
        $role_slug = ! empty( $user->roles ) ? (string) reset( $user->roles ) : '';
        $roles     = wp_roles();

        if ( $roles && $role_slug && isset( $roles->roles[ $role_slug ]['name'] ) ) {
            return translate_user_role( (string) $roles->roles[ $role_slug ]['name'] );
        }

        return $role_slug ?: __( 'Perfil editorial', 'espressivo-reportes' );
    }

    /**
     * @return string[]
     */
    private function get_reportable_type_labels(): array {
        $labels = array();

        foreach ( Espressivo_Upload_Log::reportable_post_types() as $post_type ) {
            $object = get_post_type_object( $post_type );
            if ( $object ) {
                $labels[] = (string) $object->labels->name;
            }
        }

        return array_values( array_unique( $labels ) );
    }
}
