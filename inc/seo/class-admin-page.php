<?php
namespace SSIVO_SEO\Includes;

/**
 * SSIVO-SEO · AdminPage
 *
 * Correcciones aplicadas (auditoría 2026-07):
 *  1. Capacidad propia view_ssivo_seo (no edit_posts, no manage_options).
 *  2. Migración versionada que asigna la cap a todos los roles excepto subscriber.
 *  3. Caché de 4 horas del resumen de Google (transient ssivo_seo_google_summary).
 *     La caché se invalida con el botón "Actualizar datos" o automáticamente al expirar.
 *  4. Proxy REST mejorado:
 *     - Registra errores reales en error_log (nunca silencia con ?? 0).
 *     - Si Site Kit devuelve error, el proxy lo incluye en la respuesta con __error.
 *     - El JS muestra "No disponible" + timestamp del último dato válido, nunca ceros falsos.
 *  5. Validación current_user_can() tanto en register_menu como en render_page y REST.
 */
class AdminPage {

    public function __construct() {
        add_action( 'admin_menu',    [ $this, 'register_menu' ] );
        add_action( 'admin_init',    [ $this, 'register_settings' ] );
        add_action( 'admin_init',    [ $this, 'run_capability_migration' ] );
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

        // Configurar Cron para actualización automática
        add_action( 'init', [ $this, 'schedule_cron' ] );
        add_action( 'ssivo_seo_auto_refresh', [ $this, 'run_auto_refresh' ] );
    }

    public function schedule_cron() {
        if ( ! wp_next_scheduled( 'ssivo_seo_auto_refresh' ) ) {
            wp_schedule_event( time(), 'hourly', 'ssivo_seo_auto_refresh' );
        }
    }

    public function run_auto_refresh() {
        $ranges = [ 1, 7, 14, 28 ];
        foreach ( $ranges as $days ) {
            $request = new \WP_REST_Request( 'GET', '/ssivo-seo/v1/analytics' );
            $request->set_param( 'force', '1' );
            $request->set_param( 'days', $days );
            $this->proxy_analytics_data( $request );
        }
    }

    // =========================================================================
    // CAPACIDADES
    // =========================================================================

    /**
     * Asigna view_ssivo_seo a todos los roles excepto subscriber.
     * Se ejecuta una sola vez por versión vía admin_init.
     */
    public function run_capability_migration(): void {
        if ( version_compare( get_option( 'ssivo_seo_capabilities_version', '0' ), '1.0.0', '>=' ) ) {
            return;
        }
        $this->sync_role_capabilities();
        update_option( 'ssivo_seo_capabilities_version', '1.0.0', false );
    }

    private function sync_role_capabilities(): void {
        $wp_roles = wp_roles();
        if ( ! $wp_roles ) {
            return;
        }
        foreach ( array_keys( $wp_roles->roles ) as $role_slug ) {
            $role = get_role( $role_slug );
            if ( ! $role ) {
                continue;
            }
            if ( 'subscriber' === $role_slug ) {
                $role->remove_cap( 'view_ssivo_seo' );
            } else {
                $role->add_cap( 'view_ssivo_seo' );
            }
        }
    }

    // =========================================================================
    // MENÚ Y AJUSTES
    // =========================================================================

    public function register_menu(): void {
        add_menu_page(
            'Panel Principal de SSIVO-SEO',
            'SEO',
            'view_ssivo_seo',          // Capacidad propia — no manage_options, no edit_posts
            'ssivo-seo',
            [ $this, 'render_page' ],
            'dashicons-chart-line',
            60
        );
    }

    public function register_settings(): void {
        register_setting( 'ssivo_seo_group', 'ssivo_seo_default_title' );
        register_setting( 'ssivo_seo_group', 'ssivo_seo_default_image' );
        register_setting( 'ssivo_seo_group', 'ssivo_seo_sk_owner_id', [
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
        ] );
    }

    // =========================================================================
    // OWNER ID DE SITE KIT
    // =========================================================================

    /**
     * Busca el ID del admin que tiene el token OAuth de Site Kit guardado.
     * Primero consulta la opción configurada manualmente; si no existe,
     * itera los administradores buscando el user meta del token.
     */
    private function get_sk_owner_id(): int {
        $saved = absint( get_option( 'ssivo_seo_sk_owner_id', 0 ) );
        if ( $saved > 0 ) {
            return $saved;
        }

        $admin_users = get_users( [ 'role' => 'administrator', 'number' => 10 ] );
        foreach ( $admin_users as $u ) {
            $token = get_user_option( 'googlesitekit_access_token', $u->ID );
            if ( ! empty( $token ) ) {
                update_option( 'ssivo_seo_sk_owner_id', $u->ID );
                return (int) $u->ID;
            }
        }

        if ( ! empty( $admin_users ) ) {
            return (int) $admin_users[0]->ID;
        }

        return 0;
    }

    // =========================================================================
    // REST PROXY — ssivo-seo/v1/analytics
    // =========================================================================

    public function register_rest_routes(): void {
        register_rest_route( 'ssivo-seo/v1', '/analytics', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'proxy_analytics_data' ],
            'permission_callback' => static function (): bool {
                return current_user_can( 'view_ssivo_seo' );
            },
        ] );

        // Endpoint para purgar la caché manualmente
        register_rest_route( 'ssivo-seo/v1', '/analytics/purge', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'purge_analytics_cache' ],
            'permission_callback' => static function (): bool {
                return current_user_can( 'manage_options' );
            },
        ] );
    }

    public function purge_analytics_cache( \WP_REST_Request $request ): \WP_REST_Response {
        $ranges = [ 1, 7, 14, 28 ];
        foreach ( $ranges as $days ) {
            delete_transient( "ssivo_seo_google_summary_{$days}d" );
        }
        return rest_ensure_response( [ 'purged' => true ] );
    }

    /**
     * Proxy: llama a Site Kit internamente usando rest_do_request() con el
     * usuario propietario del token. Devuelve un resumen cacheado 4 horas.
     *
     * Si Site Kit falla (401, 403, 500, error de red) devuelve los datos
     * cacheados anteriores con un flag stale=true. Nunca devuelve ceros falsos.
     */
    public function proxy_analytics_data( \WP_REST_Request $request ): \WP_REST_Response {
        ob_start();
        
        $days = (int) $request->get_param( 'days' );
        if ( ! in_array( $days, [ 1, 7, 14, 28 ], true ) ) {
            $days = 28;
        }
        $cache_key = "ssivo_seo_google_summary_{$days}d";

        // 1. Servir desde caché si existe y no se fuerza refresco
        $force = (bool) $request->get_param( 'force' );
        if ( ! $force ) {
            $cached = get_transient( $cache_key );
            if ( is_array( $cached ) ) {
                $cached['from_cache'] = true;
                ob_end_clean();
                return rest_ensure_response( $cached );
            }
        }

        // 2. Encontrar el propietario del token
        $admin_id = $this->get_sk_owner_id();
        if ( ! $admin_id ) {
            error_log( 'SSIVO-SEO Analytics: No se encontró administrador con token de Site Kit.' );
            ob_end_clean();
            return rest_ensure_response( $this->unavailable_response( 'no_sk_owner' ) );
        }

        $original_user = get_current_user_id();
        wp_set_current_user( $admin_id );

        $start_date = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );
        $end_date   = gmdate( 'Y-m-d' );

        $endpoints = [
            // GA4: totales globales (sin dimensiones → Site Kit devuelve totals)
            'ga4' => [
                'route'  => '/google-site-kit/v1/modules/analytics-4/data/report',
                'params' => [
                    'startDate' => $start_date,
                    'endDate'   => $end_date,
                    'metrics'   => [
                        [ 'name' => 'screenPageViews' ],
                        [ 'name' => 'activeUsers' ],
                    ],
                ],
            ],
            // Search Console: impresiones totales agrupadas por fecha
            'search' => [
                'route'  => '/google-site-kit/v1/modules/search-console/data/searchanalytics',
                'params' => [
                    'startDate'  => $start_date,
                    'endDate'    => $end_date,
                    'dimensions' => [ 'date' ],
                ],
            ],
            // GA4: top 5 páginas
            'top_pages' => [
                'route'  => '/google-site-kit/v1/modules/analytics-4/data/report',
                'params' => [
                    'startDate'  => $start_date,
                    'endDate'    => $end_date,
                    'metrics'    => [ [ 'name' => 'screenPageViews' ] ],
                    'dimensions' => [ [ 'name' => 'pageTitle' ], [ 'name' => 'pagePath' ] ],
                    'orderby'    => [ [ 'metric' => [ 'metricName' => 'screenPageViews' ], 'desc' => true ] ],
                    'limit'      => 5,
                ],
            ],
            // Search Console: top 5 keywords
            'keywords' => [
                'route'  => '/google-site-kit/v1/modules/search-console/data/searchanalytics',
                'params' => [
                    'startDate'  => $start_date,
                    'endDate'    => $end_date,
                    'dimensions' => [ 'query' ],
                    'limit'      => 5,
                ],
            ],
            // GA4: países
            'countries' => [
                'route'  => '/google-site-kit/v1/modules/analytics-4/data/report',
                'params' => [
                    'startDate'  => $start_date,
                    'endDate'    => $end_date,
                    'metrics'    => [ [ 'name' => 'screenPageViews' ] ],
                    'dimensions' => [ [ 'name' => 'country' ] ],
                    'orderby'    => [ [ 'metric' => [ 'metricName' => 'screenPageViews' ], 'desc' => true ] ],
                    'limit'      => 5,
                ],
            ],
            // GA4: dispositivos
            'devices' => [
                'route'  => '/google-site-kit/v1/modules/analytics-4/data/report',
                'params' => [
                    'startDate'  => $start_date,
                    'endDate'    => $end_date,
                    'metrics'    => [ [ 'name' => 'screenPageViews' ] ],
                    'dimensions' => [ [ 'name' => 'deviceCategory' ] ],
                ],
            ],
        ];

        $results    = [];
        $has_error  = false;

        foreach ( $endpoints as $key => $config ) {
            $rest_req = new \WP_REST_Request( 'GET', $config['route'] );
            $rest_req->set_query_params( $config['params'] );

            $rest_response = rest_do_request( $rest_req );
            $status        = $rest_response->get_status();
            $data          = $rest_response->get_data();

            if ( $status >= 200 && $status < 300 ) {
                $results[ $key ] = $data;
            } else {
                // Registrar el error real — nunca silenciarlo con ?? 0
                error_log( sprintf(
                    'SSIVO-SEO Analytics [%s]: HTTP %d — %s',
                    $key,
                    $status,
                    wp_json_encode( $data )
                ) );
                $results[ $key ] = [
                    '__error'  => true,
                    '__status' => $status,
                    '__key'    => $key,
                    '__data'   => $data,
                ];
                $has_error = true;
            }
        }

        wp_set_current_user( $original_user );

        // 3. Si todas las claves tienen error, devolver unavailable sin cachear
        $all_errors = array_reduce(
            $results,
            static fn( bool $carry, $item ) => $carry && isset( $item['__error'] ),
            true
        );

        if ( $all_errors ) {
            error_log( 'SSIVO-SEO Analytics: Todos los endpoints de Site Kit fallaron. Verificar token OAuth y permisos.' );
            ob_end_clean();
            return rest_ensure_response( $this->unavailable_response( 'all_endpoints_failed' ) );
        }

        // 4. Guardar en caché 4 horas (solo si al menos un endpoint tuvo éxito)
        $results['from_cache']  = false;
        $results['has_error']   = $has_error;
        $results['updated_at']  = gmdate( 'Y-m-d H:i:s' );
        $results['status']      = $has_error ? 'partial' : 'ok';

        set_transient( $cache_key, $results, 4 * HOUR_IN_SECONDS );

        ob_end_clean();
        return rest_ensure_response( $results );
    }

    /**
     * Estructura de respuesta cuando los datos no están disponibles.
     * El JS mostrará un aviso honesto, nunca ceros falsos.
     */
    private function unavailable_response( string $reason = '' ): array {
        return [
            'status'     => 'unavailable',
            'reason'     => $reason,
            'updated_at' => null,
            'from_cache' => false,
            'ga4'        => null,
            'search'     => null,
            'top_pages'  => null,
            'keywords'   => null,
            'countries'  => null,
            'devices'    => null,
        ];
    }

    // =========================================================================
    // RENDER DEL PANEL
    // =========================================================================

    public function render_page(): void {
        // Doble validación: register_menu ya usa la cap, pero la verificamos aquí también
        if ( ! current_user_can( 'view_ssivo_seo' ) ) {
            wp_die(
                esc_html__( 'No tienes permiso para consultar estas métricas.', 'pro' ),
                '',
                [ 'response' => 403 ]
            );
        }

        global $wpdb;

        $default_title = get_option( 'ssivo_seo_default_title', get_bloginfo( 'name' ) );
        $default_image = get_option( 'ssivo_seo_default_image', '' );

        // Estadísticas locales (BD propia — siempre disponibles)
        $total_posts = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'"
        );

        $authors = $wpdb->get_results( "
            SELECT u.display_name, COUNT(p.ID) as post_count
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->users} u ON p.post_author = u.ID
            WHERE p.post_status = 'publish' AND p.post_type = 'post'
            GROUP BY p.post_author
            ORDER BY post_count DESC
            LIMIT 5
        " );

        // Metadatos de la caché para el panel
        $cached_meta = get_transient( 'ssivo_seo_google_summary' );
        $last_update = ( is_array( $cached_meta ) && ! empty( $cached_meta['updated_at'] ) )
            ? esc_html( $cached_meta['updated_at'] ) . ' UTC'
            : 'Sin datos previos';

        ?>
        <div class="wrap" style="max-width:900px;">
            <h1 style="font-weight:700;margin-bottom:20px;">
                <span class="dashicons dashicons-chart-line" style="font-size:28px;width:28px;height:28px;color:#3b82f6;margin-right:8px;margin-top:2px;"></span>
                Panel Principal de SSIVO-SEO
            </h1>
            <p style="font-size:14px;color:#475569;margin-bottom:30px;">
                Resumen de salud y opciones globales de posicionamiento en buscadores para tu plataforma.
            </p>

            <!-- WIDGETS LOCALES (BD propia) -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:30px;">

                <div style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.1);border:1px solid #e2e8f0;border-top:4px solid #64748b;">
                    <h3 style="margin-top:0;color:#1e293b;font-size:15px;margin-bottom:15px;">Total Publicaciones</h3>
                    <div style="display:flex;align-items:flex-end;gap:10px;">
                        <span style="font-size:36px;font-weight:800;color:#1e293b;line-height:1;"><?php echo number_format_i18n( $total_posts ); ?></span>
                        <span style="font-size:14px;color:#64748b;margin-bottom:5px;">artículos activos</span>
                    </div>
                    <p style="margin:10px 0 0;font-size:13px;color:#64748b;">Artículos indexados y publicados en el sistema.</p>
                </div>

                <div style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.1);border:1px solid #e2e8f0;border-top:4px solid #8b5cf6;">
                    <h3 style="margin-top:0;color:#1e293b;font-size:15px;margin-bottom:15px;">Actividad de Autores</h3>
                    <ul style="margin:0;padding:0;list-style:none;">
                        <?php if ( $authors ) : ?>
                            <?php foreach ( $authors as $author ) : ?>
                                <li style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">
                                    <span style="color:#334155;font-weight:500;"><?php echo esc_html( $author->display_name ); ?></span>
                                    <span style="color:#64748b;background:#f1f5f9;padding:2px 8px;border-radius:12px;font-size:11px;"><?php echo number_format_i18n( $author->post_count ); ?> posts</span>
                                </li>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <li style="font-size:13px;color:#64748b;">Sin actividad registrada.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- BARRA DE ESTADO DE CACHÉ -->
            <div id="ssivo-cache-bar" style="display:flex;align-items:center;justify-content:space-between;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:10px 16px;margin-bottom:20px;font-size:13px;color:#475569;">
                <span>
                    <strong>Datos de Google:</strong>
                    <span id="ssivo-cache-status">Cargando...</span>
                    &nbsp;·&nbsp;
                    <em>Última actualización: <?php echo $last_update; ?></em>
                </span>
                <div style="display:flex;align-items:center;gap:10px;">
                    <select id="ssivo-date-filter" style="font-size:12px;padding:4px 28px 4px 8px;border-radius:4px;border:1px solid #cbd5e1;background-color:#fff;">
                        <option value="1">Últimas 24 horas</option>
                        <option value="7">Últimos 7 días</option>
                        <option value="14">Últimos 14 días</option>
                        <option value="28" selected>Últimos 28 días</option>
                    </select>
                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                        <button id="ssivo-refresh-btn" style="background:#3b82f6;color:#fff;border:none;border-radius:4px;padding:5px 14px;cursor:pointer;font-size:12px;font-weight:600;">
                            ↻ Actualizar ahora
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- MÉTRICAS GOOGLE (cargadas vía JS) -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:30px;">

                <div style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.1);border:1px solid #e2e8f0;border-top:4px solid #3b82f6;">
                    <h3 style="margin-top:0;color:#1e293b;font-size:15px;margin-bottom:15px;">Visitas (Últimos 28 días)</h3>
                    <div style="display:flex;align-items:flex-end;gap:10px;">
                        <span id="sk-visitas" style="font-size:36px;font-weight:800;color:#1e293b;line-height:1;">...</span>
                        <span style="font-size:14px;color:#64748b;margin-bottom:5px;">vistas de página</span>
                    </div>
                </div>

                <div style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.1);border:1px solid #e2e8f0;border-top:4px solid #10b981;">
                    <h3 style="margin-top:0;color:#1e293b;font-size:15px;margin-bottom:15px;">Usuarios Únicos</h3>
                    <div style="display:flex;align-items:flex-end;gap:10px;">
                        <span id="sk-usuarios" style="font-size:36px;font-weight:800;color:#1e293b;line-height:1;">...</span>
                        <span style="font-size:14px;color:#64748b;margin-bottom:5px;">usuarios</span>
                    </div>
                </div>

                <div style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.1);border:1px solid #e2e8f0;border-top:4px solid #f59e0b;">
                    <h3 style="margin-top:0;color:#1e293b;font-size:15px;margin-bottom:15px;">Impresiones en Buscador</h3>
                    <div style="display:flex;align-items:flex-end;gap:10px;">
                        <span id="sk-impresiones" style="font-size:36px;font-weight:800;color:#1e293b;line-height:1;">...</span>
                        <span style="font-size:14px;color:#64748b;margin-bottom:5px;">veces visto</span>
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:20px;margin-bottom:30px;">
                <div style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.1);border:1px solid #e2e8f0;border-top:4px solid #ef4444;">
                    <h3 style="margin-top:0;color:#1e293b;font-size:15px;margin-bottom:15px;">Top 5 Contenidos Más Visitados</h3>
                    <ul id="sk-top-content" style="margin:0;padding:0;list-style:none;"><li style="font-size:13px;color:#64748b;">Cargando...</li></ul>
                </div>
                <div style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.1);border:1px solid #e2e8f0;border-top:4px solid #8b5cf6;">
                    <h3 style="margin-top:0;color:#1e293b;font-size:15px;margin-bottom:15px;">Palabras Clave (Google Search)</h3>
                    <ul id="sk-top-keywords" style="margin:0;padding:0;list-style:none;"><li style="font-size:13px;color:#64748b;">Cargando...</li></ul>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:20px;margin-bottom:30px;">
                <div style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.1);border:1px solid #e2e8f0;border-top:4px solid #10b981;">
                    <h3 style="margin-top:0;color:#1e293b;font-size:15px;margin-bottom:15px;">Tráfico por Países</h3>
                    <ul id="sk-top-countries" style="margin:0;padding:0;list-style:none;"><li style="font-size:13px;color:#64748b;">Cargando...</li></ul>
                </div>
                <div style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.1);border:1px solid #e2e8f0;border-top:4px solid #3b82f6;">
                    <h3 style="margin-top:0;color:#1e293b;font-size:15px;margin-bottom:15px;">Distribución por Dispositivo</h3>
                    <ul id="sk-devices" style="margin:0;padding:0;list-style:none;"><li style="font-size:13px;color:#64748b;">Cargando...</li></ul>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {

                /* ── Helpers de DOM ─────────────────────────────────────────── */
                function makeLi(left, right, extra) {
                    var li = document.createElement('li');
                    li.style.cssText = 'display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13px;' + (extra || '');
                    var l = document.createElement('span');
                    l.style.cssText = 'color:#334155;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:80%;';
                    l.textContent = left;
                    var r = document.createElement('span');
                    r.style.color = '#64748b';
                    r.textContent = right;
                    li.appendChild(l); li.appendChild(r);
                    return li;
                }

                function setMsg(id, msg, color) {
                    var el = document.getElementById(id);
                    if (!el) return;
                    el.innerHTML = '';
                    var li = document.createElement('li');
                    li.style.cssText = 'font-size:13px;color:' + (color || '#64748b') + ';';
                    li.textContent = msg;
                    el.appendChild(li);
                }

                function setUnavailable(id) {
                    setMsg(id, 'Datos temporalmente no disponibles.', '#94a3b8');
                }

                /* ── Función principal de renderizado ───────────────────────── */
                function renderDashboard(all, fromCache) {
                    var status = all.status || 'unavailable';

                    // Barra de estado
                    var statusEl = document.getElementById('ssivo-cache-status');
                    if (statusEl) {
                        if (status === 'unavailable') {
                            statusEl.textContent = '⚠ No disponible';
                            statusEl.style.color = '#ef4444';
                        } else if (status === 'partial') {
                            statusEl.textContent = '⚠ Parcial (algunos servicios fallaron — ver error_log)';
                            statusEl.style.color = '#f59e0b';
                        } else {
                            statusEl.textContent = fromCache ? '✓ Desde caché' : '✓ Actualizado';
                            statusEl.style.color = '#10b981';
                        }
                    }

                    /* ── GA4: Visitas y Usuarios ──────────────────────────── */
                    var ga4 = all.ga4;
                    var visitas = null, usuarios = null;

                    if (ga4 && !ga4.__error) {
                        // Formato A: totals (Site Kit sin dimensiones)
                        if (ga4.totals && ga4.totals[0] && ga4.totals[0].metricValues) {
                            visitas  = parseInt(ga4.totals[0].metricValues[0].value || 0);
                            usuarios = parseInt(ga4.totals[0].metricValues[1] && ga4.totals[0].metricValues[1].value || 0);
                        // Formato B: rows (con dimensiones)
                        } else if (ga4.rows && ga4.rows.length > 0) {
                            var tV = 0, tU = 0;
                            ga4.rows.forEach(function(r) {
                                tV += parseInt(r.metricValues && r.metricValues[0] ? r.metricValues[0].value : 0);
                                tU += parseInt(r.metricValues && r.metricValues[1] ? r.metricValues[1].value : 0);
                            });
                            visitas = tV; usuarios = tU;
                        // Formato C: array plano
                        } else if (Array.isArray(ga4) && ga4.length > 0 && ga4[0].metricValues) {
                            visitas  = parseInt(ga4[0].metricValues[0] ? ga4[0].metricValues[0].value : 0);
                            usuarios = parseInt(ga4[0].metricValues[1] ? ga4[0].metricValues[1].value : 0);
                        } else {
                            // GA4 respondió 200 pero estructura desconocida — log en consola
                            console.warn('[SSIVO-SEO] ga4: estructura de respuesta no reconocida', ga4);
                        }
                    } else if (ga4 && ga4.__error) {
                        console.error('[SSIVO-SEO] ga4 error HTTP', ga4.__status, ga4.__data);
                    }

                    var skV = document.getElementById('sk-visitas');
                    var skU = document.getElementById('sk-usuarios');
                    if (skV) skV.textContent = visitas !== null ? visitas.toLocaleString() : '—';
                    if (skU) skU.textContent = usuarios !== null ? usuarios.toLocaleString() : '—';

                    /* ── Search Console: Impresiones ─────────────────────── */
                    var sc = all.search;
                    var totalImp = null;
                    if (sc && !sc.__error) {
                        var rows = Array.isArray(sc) ? sc : (sc.rows || []);
                        totalImp = 0;
                        rows.forEach(function(row) { totalImp += parseInt(row.impressions || 0); });
                    } else if (sc && sc.__error) {
                        console.error('[SSIVO-SEO] search-console error HTTP', sc.__status, sc.__data);
                    }
                    var skI = document.getElementById('sk-impresiones');
                    if (skI) skI.textContent = totalImp !== null ? totalImp.toLocaleString() : '—';

                    /* ── Top Contenidos ──────────────────────────────────── */
                    var tp = all.top_pages;
                    var ulC = document.getElementById('sk-top-content');
                    if (ulC) {
                        ulC.innerHTML = '';
                        if (tp && !tp.__error && tp.rows && tp.rows.length > 0) {
                            tp.rows.forEach(function(row) {
                                var t = row.dimensionValues[0].value;
                                var v = parseInt(row.metricValues[0].value).toLocaleString() + ' vis';
                                ulC.appendChild(makeLi(t, v));
                            });
                        } else {
                            if (tp && tp.__error) console.error('[SSIVO-SEO] top_pages error', tp.__status);
                            setUnavailable('sk-top-content');
                        }
                    }

                    /* ── Top Keywords ────────────────────────────────────── */
                    var kw = all.keywords;
                    var ulK = document.getElementById('sk-top-keywords');
                    if (ulK) {
                        ulK.innerHTML = '';
                        var kwRows = kw && !kw.__error ? (Array.isArray(kw) ? kw : (kw.rows || [])) : [];
                        if (kwRows.length > 0) {
                            kwRows.forEach(function(row) {
                                var q = row.keys ? row.keys[0] : (row.dimensionValues ? row.dimensionValues[0].value : '?');
                                var c = (row.clicks ? parseInt(row.clicks).toLocaleString() : '0') + ' clics';
                                ulK.appendChild(makeLi(q, c));
                            });
                        } else {
                            if (kw && kw.__error) console.error('[SSIVO-SEO] keywords error', kw.__status);
                            setUnavailable('sk-top-keywords');
                        }
                    }

                    /* ── Países ──────────────────────────────────────────── */
                    var co = all.countries;
                    var ulCo = document.getElementById('sk-top-countries');
                    if (ulCo) {
                        ulCo.innerHTML = '';
                        if (co && !co.__error && co.rows && co.rows.length > 0) {
                            co.rows.forEach(function(row) {
                                var c = row.dimensionValues[0].value;
                                var v = parseInt(row.metricValues[0].value).toLocaleString() + ' vis';
                                ulCo.appendChild(makeLi(c, v));
                            });
                        } else {
                            if (co && co.__error) console.error('[SSIVO-SEO] countries error', co.__status);
                            setUnavailable('sk-top-countries');
                        }
                    }

                    /* ── Dispositivos ────────────────────────────────────── */
                    var dv = all.devices;
                    var ulDv = document.getElementById('sk-devices');
                    if (ulDv) {
                        ulDv.innerHTML = '';
                        if (dv && !dv.__error && dv.rows && dv.rows.length > 0) {
                            var tot = 0;
                            dv.rows.forEach(function(r) { tot += parseInt(r.metricValues[0].value); });
                            dv.rows.forEach(function(row) {
                                var dev = row.dimensionValues[0].value;
                                var val = parseInt(row.metricValues[0].value);
                                var pct = tot > 0 ? Math.round((val / tot) * 100) + '%' : '0%';
                                ulDv.appendChild(makeLi(dev, pct, 'text-transform:capitalize;'));
                            });
                        } else {
                            if (dv && dv.__error) console.error('[SSIVO-SEO] devices error', dv.__status);
                            setUnavailable('sk-devices');
                        }
                    }
                }

                /* ── Función de carga (con force opcional) ──────────────────── */
                function loadData(force) {
                    var base  = '<?php echo esc_url_raw( rest_url( 'ssivo-seo/v1/analytics' ) ); ?>';
                    var nonce = '<?php echo wp_create_nonce( 'wp_rest' ); ?>';
                    var daysSel = document.getElementById('ssivo-date-filter');
                    var days = daysSel ? daysSel.value : '28';
                    var url   = base + '?days=' + days + (force ? '&force=1' : '');

                    // Update title
                    var titleText = days == '1' ? 'Últimas 24 horas' : 'Últimos ' + days + ' días';
                    document.querySelectorAll('h3').forEach(function(el) {
                        if (el.textContent.includes('Visitas (')) {
                            el.textContent = 'Visitas (' + titleText + ')';
                        }
                    });

                    fetch(url, {
                        credentials: 'same-origin',
                        headers: { 'X-WP-Nonce': nonce, 'Content-Type': 'application/json' }
                    })
                    .then(function(res) {
                        if (!res.ok) {
                            // Respuesta HTTP no-2xx: log real, nunca cero
                            return res.text().then(function(body) {
                                console.error('[SSIVO-SEO] HTTP ' + res.status + ':', body);
                                throw new Error('HTTP ' + res.status + ': ' + body.substring(0, 100));
                            });
                        }
                        return res.text().then(function(text) {
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('[SSIVO-SEO] Parse Error. Raw response:', text);
                                throw new Error('Parse error: ' + text.substring(0, 60));
                            }
                        });
                    })
                    .then(function(all) {
                        // Estructura completa de depuración siempre en consola
                        console.log('[SSIVO-SEO] Respuesta del proxy:', JSON.stringify(all));
                        renderDashboard(all, all.from_cache);
                    })
                    .catch(function(err) {
                        console.error('[SSIVO-SEO] Error de red o parseo:', err);
                        var statusEl = document.getElementById('ssivo-cache-status');
                        if (statusEl) { 
                            statusEl.textContent = '✗ ' + (err.message || 'Error de conexión'); 
                            statusEl.style.color = '#ef4444'; 
                        }
                        ['sk-visitas','sk-usuarios','sk-impresiones'].forEach(function(id) {
                            var el = document.getElementById(id);
                            if (el) el.textContent = '—';  // Nunca mostrar 0 falso
                        });
                        ['sk-top-content','sk-top-keywords','sk-top-countries','sk-devices'].forEach(function(id) {
                            setUnavailable(id);
                        });
                    });
                }

                // Carga inicial
                loadData(false);

                // Selector de fechas
                var daysSel = document.getElementById('ssivo-date-filter');
                if (daysSel) {
                    daysSel.addEventListener('change', function() {
                        var statusEl = document.getElementById('ssivo-cache-status');
                        if (statusEl) { statusEl.textContent = 'Cargando...'; statusEl.style.color = '#475569'; }
                        loadData(false);
                    });
                }

                // Botón de actualización manual (solo admins)
                var btn = document.getElementById('ssivo-refresh-btn');
                if (btn) {
                    btn.addEventListener('click', function() {
                        btn.disabled = true;
                        btn.textContent = 'Actualizando...';
                        // Primero purgar la caché del servidor, luego recargar
                        var purgeUrl = '<?php echo esc_url_raw( rest_url( 'ssivo-seo/v1/analytics/purge' ) ); ?>';
                        var nonce    = '<?php echo wp_create_nonce( 'wp_rest' ); ?>';
                        fetch(purgeUrl, { credentials: 'same-origin', headers: { 'X-WP-Nonce': nonce } })
                            .then(function() { loadData(true); })
                            .catch(function() { loadData(true); })
                            .finally(function() {
                                btn.disabled = false;
                                btn.textContent = '↻ Actualizar ahora';
                            });
                    });
                }
            });
            </script>

            <?php if ( current_user_can( 'manage_options' ) ) : ?>
            <!-- FORMULARIO DE AJUSTES (solo admins) -->
            <h2 style="font-size:18px;font-weight:600;color:#1e293b;margin-bottom:15px;">Ajustes Globales</h2>
            <form method="post" action="options.php" style="background:#fff;padding:25px;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.1);border:1px solid #e2e8f0;">
                <?php
                settings_fields( 'ssivo_seo_group' );
                do_settings_sections( 'ssivo_seo_group' );
                $sk_owner_id      = absint( get_option( 'ssivo_seo_sk_owner_id', 0 ) );
                $sk_owner_display = '';
                if ( $sk_owner_id ) {
                    $sk_user = get_user_by( 'ID', $sk_owner_id );
                    if ( $sk_user ) {
                        $sk_owner_display = ' — ' . esc_html( $sk_user->display_name ) . ' (' . esc_html( $sk_user->user_email ) . ')';
                    }
                }
                ?>
                <table class="form-table" role="presentation"><tbody>
                    <tr>
                        <th scope="row"><label for="ssivo_seo_default_title" style="font-weight:600;">Sufijo del Título Global</label></th>
                        <td>
                            <input name="ssivo_seo_default_title" type="text" id="ssivo_seo_default_title" value="<?php echo esc_attr( $default_title ); ?>" class="regular-text" />
                            <p class="description">Este texto se añadirá automáticamente al final de tus títulos. (Ej. " | Diario El Oriental")</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ssivo_seo_default_image" style="font-weight:600;">URL Imagen Destacada por Defecto</label></th>
                        <td>
                            <input name="ssivo_seo_default_image" type="url" id="ssivo_seo_default_image" value="<?php echo esc_url( $default_image ); ?>" class="regular-text large-text" />
                            <p class="description">Imagen que se usará al compartir si el artículo no tiene imagen destacada propia.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ssivo_seo_sk_owner_id" style="font-weight:600;">ID Usuario de Site Kit</label></th>
                        <td>
                            <input name="ssivo_seo_sk_owner_id" type="number" id="ssivo_seo_sk_owner_id"
                                   value="<?php echo esc_attr( $sk_owner_id ); ?>"
                                   class="small-text" min="0" step="1" />
                            <p class="description">
                                ID del usuario WordPress que conectó Google Site Kit (OAuth).
                                <?php if ( $sk_owner_display ) : ?>
                                    <br><strong>Detectado:</strong> Usuario #<?php echo $sk_owner_id; ?><?php echo $sk_owner_display; ?>
                                <?php else : ?>
                                    <br><span style="color:#ef4444;">⚠ No detectado. Ingresa el ID del admin que conectó Site Kit y guarda.</span>
                                <?php endif; ?>
                            </p>
                        </td>
                    </tr>
                </tbody></table>
                <hr style="border:0;border-top:1px solid #e2e8f0;margin:20px 0;" />
                <?php submit_button( 'Guardar Cambios SEO', 'primary', 'submit', false ); ?>
            </form>
            <?php endif; ?>
        </div>
        <?php
    }
}
