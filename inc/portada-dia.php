<?php
/**
 * @author Arturo Merchan | Merchan.Dev | Espressivo Venezuela,C.A
 * 
 * ADVERTENCIA LEGAL:
 * Queda totalmente prohibida su reproduccion, edicion, venta, propaganda, alteracion 
 * o cualquier otra accion que de una u otra forma violente la propiedad intelectual, 
 * material y digital de este proyecto. Esta infraccion esta prohibida y penada por la ley.
 */
/**
 * Gestor de la Portada del Día
 * Permite gestionar la portada actual y la portada programada de reemplazo para las 05:00 AM.
 *
 * @package Edit-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registrar la página de ajustes de Portada del Día
 */
function pro_portada_dia_settings_page() {
    add_menu_page(
        'Portada del Día',
        'Portada del Día',
        'edit_posts', // Dirección y Administradores tienen acceso
        'portada-dia',
        'pro_portada_dia_settings_render',
        'dashicons-format-image',
        56 // Ubicación después de Páginas
    );
}
add_action( 'admin_menu', 'pro_portada_dia_settings_page', 20 );

/**
 * Cargar recursos necesarios de WP Media Uploader para la página
 */
function pro_portada_dia_admin_assets( $hook ) {
    if ( 'toplevel_page_portada-dia' !== $hook ) {
        return;
    }
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'pro_portada_dia_admin_assets' );

/**
 * Hook para procesar el reemplazo programado a las 05:00 AM en el frontend o backend
 */
function pro_check_scheduled_portada_swap() {
    $reemplazo = get_option( 'pro_portada_reemplazo' );
    $fecha = get_option( 'pro_portada_reemplazo_fecha' ); // YYYY-MM-DD
    
    if ( $reemplazo && $fecha ) {
        $timezone = new DateTimeZone( 'America/Caracas' );
        $now = new DateTime( 'now', $timezone );
        $scheduled_time = new DateTime( $fecha . ' 05:00:00', $timezone );
        
        if ( $now >= $scheduled_time ) {
            // Intercambiar la portada actual con el reemplazo
            update_option( 'pro_portada_actual', $reemplazo );
            // Limpiar los valores de reemplazo programados
            delete_option( 'pro_portada_reemplazo' );
            delete_option( 'pro_portada_reemplazo_fecha' );
        }
    }
}
add_action( 'init', 'pro_check_scheduled_portada_swap' );

/**
 * Renderizado del Panel de Ajustes
 */
function pro_portada_dia_settings_render() {
    // Procesar envío del formulario
    if ( isset( $_POST['pro_portada_dia_save'] ) && check_admin_referer( 'pro_portada_dia_nonce_action', 'pro_portada_dia_nonce' ) ) {
        if ( isset( $_POST['pro_portada_actual'] ) ) {
            update_option( 'pro_portada_actual', esc_url_raw( $_POST['pro_portada_actual'] ) );
        }
        if ( isset( $_POST['pro_portada_reemplazo'] ) ) {
            update_option( 'pro_portada_reemplazo', esc_url_raw( $_POST['pro_portada_reemplazo'] ) );
        }
        if ( isset( $_POST['pro_portada_reemplazo_fecha'] ) ) {
            update_option( 'pro_portada_reemplazo_fecha', sanitize_text_field( $_POST['pro_portada_reemplazo_fecha'] ) );
        }
        echo '<div class="notice notice-success is-dismissible"><p><strong>¡Ajustes de portada guardados correctamente!</strong></p></div>';
    }

    $portada_actual = get_option( 'pro_portada_actual', '' );
    $portada_reemplazo = get_option( 'pro_portada_reemplazo', '' );
    $portada_reemplazo_fecha = get_option( 'pro_portada_reemplazo_fecha', '' );
    ?>
    <div class="wrap" style="max-width: 900px; margin: 30px 0 0 20px;">
        <h1 style="font-weight: 700; font-size: 2.2rem; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span class="dashicons dashicons-format-image" style="font-size: 32px; width: 32px; height: 32px; line-height: 32px; color: #3b82f6;"></span>
            Portada del Día
        </h1>
        <p style="color: #64748b; font-size: 1.1rem; margin-bottom: 30px;">
            Administra la portada de revista o periódico que se mostrará de forma destacada en la página de inicio (Home). 
            Puedes programar una portada de reemplazo para que se publique automáticamente a las <strong>05:00 AM</strong> del día seleccionado.
        </p>

        <form method="post" action="">
            <?php wp_nonce_field( 'pro_portada_dia_nonce_action', 'pro_portada_dia_nonce' ); ?>

            <!-- SECCIÓN PORTADA ACTUAL -->
            <div style="background: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; padding: 30px; margin-bottom: 30px;">
                <h2 style="font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-visibility" style="color: #10b981;"></span>
                    Portada Actual Publicada (Home)
                </h2>
                
                <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
                    <div id="preview-portada-actual" style="flex-shrink: 0; width: 180px;">
                        <?php if ( $portada_actual ) : ?>
                            <img src="<?php echo esc_url($portada_actual); ?>" style="max-height: 250px; width: 100%; object-fit: contain; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                        <?php else : ?>
                            <div style="background:#f1f5f9; padding:40px 20px; border-radius:8px; color:#94a3b8; text-align:center; border:2px dashed #cbd5e1;">Sin imagen seleccionada</div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="flex-grow: 1;">
                        <p style="font-weight: 600; color: #334155; margin-bottom: 8px;">Imagen de Portada Activa:</p>
                        <input type="text" name="pro_portada_actual" id="input-portada-actual" value="<?php echo esc_url($portada_actual); ?>" style="width: 100%; max-width: 500px; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; margin-bottom: 15px;" readonly>
                        
                        <div>
                            <button type="button" class="button button-primary pro-upload-btn" data-input-id="input-portada-actual" data-preview-id="preview-portada-actual" style="background:#10b981; border-color:#10b981; font-weight:600; height:36px; padding:0 16px;">
                                <span class="dashicons dashicons-admin-media" style="margin-top: 5px;"></span> Seleccionar de Medios
                            </button>
                            <button type="button" class="button pro-remove-btn" data-input-id="input-portada-actual" data-preview-id="preview-portada-actual" style="margin-left: 8px; color: #ef4444; border-color: #ef4444; font-weight:600; height:36px; display: <?php echo $portada_actual ? 'inline-block' : 'none'; ?>;">
                                Quitar Imagen
                            </button>
                        </div>
                        <p style="color: #94a3b8; font-size: 0.85rem; margin-top: 10px; line-height: 1.4;">
                            * Esta es la imagen activa en la Home en este instante. Si cometes un error o quieres cambiarla inmediatamente, súbela aquí.
                        </p>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN PORTADA PROGRAMADA DE REEMPLAZO -->
            <div style="background: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; padding: 30px; margin-bottom: 40px;">
                <h2 style="font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 20px; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-calendar-alt" style="color: #3b82f6;"></span>
                    Reemplazo Programado de Portada (05:00 AM)
                </h2>
                
                <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
                    <div id="preview-portada-reemplazo" style="flex-shrink: 0; width: 180px;">
                        <?php if ( $portada_reemplazo ) : ?>
                            <img src="<?php echo esc_url($portada_reemplazo); ?>" style="max-height: 250px; width: 100%; object-fit: contain; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                        <?php else : ?>
                            <div style="background:#f1f5f9; padding:40px 20px; border-radius:8px; color:#94a3b8; text-align:center; border:2px dashed #cbd5e1;">Sin imagen seleccionada</div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="flex-grow: 1;">
                        <p style="font-weight: 600; color: #334155; margin-bottom: 8px;">Imagen de Portada Nueva (Próxima Edición):</p>
                        <input type="text" name="pro_portada_reemplazo" id="input-portada-reemplazo" value="<?php echo esc_url($portada_reemplazo); ?>" style="width: 100%; max-width: 500px; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; margin-bottom: 15px;" readonly>
                        
                        <div style="margin-bottom: 25px;">
                            <button type="button" class="button button-primary pro-upload-btn" data-input-id="input-portada-reemplazo" data-preview-id="preview-portada-reemplazo" style="background:#3b82f6; border-color:#3b82f6; font-weight:600; height:36px; padding:0 16px;">
                                <span class="dashicons dashicons-admin-media" style="margin-top: 5px;"></span> Seleccionar de Medios
                            </button>
                            <button type="button" class="button pro-remove-btn" data-input-id="input-portada-reemplazo" data-preview-id="preview-portada-reemplazo" style="margin-left: 8px; color: #ef4444; border-color: #ef4444; font-weight:600; height:36px; display: <?php echo $portada_reemplazo ? 'inline-block' : 'none'; ?>;">
                                Quitar Imagen
                            </button>
                        </div>

                        <!-- FECHA PROGRAMADA -->
                        <div style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; padding: 20px; max-width: 500px;">
                            <p style="font-weight: 700; color: #1e293b; margin-top: 0; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                <span class="dashicons dashicons-clock" style="color: #64748b;"></span>
                                ¿Cuándo debe ocurrir el reemplazo?
                            </p>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <input type="date" name="pro_portada_reemplazo_fecha" value="<?php echo esc_attr($portada_reemplazo_fecha); ?>" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: 600; color: #334155;">
                                <span style="font-weight: 700; color: #475569; font-size: 1.05rem;">a las 05:00 AM</span>
                            </div>
                            <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 0; margin-top: 12px; line-height: 1.4;">
                                * A las 05:00 AM de la fecha ingresada, WordPress reemplazará automáticamente la Portada Actual por la Portada Nueva y vaciará este formulario para la siguiente programación.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTONES DE ENVÍO -->
            <div style="border-top: 1px solid #cbd5e1; padding-top: 20px; display: flex; align-items: center; gap: 15px;">
                <input type="submit" name="pro_portada_dia_save" class="button button-primary button-large" value="Guardar Ajustes de Portada" style="background:#0f172a; border-color:#0f172a; font-weight:700; height:46px; padding:0 30px; border-radius:8px; font-size:1rem; box-shadow:0 4px 10px rgba(15,23,42,0.15);">
                
                <!-- Mostrar Hora del Servidor para referencia del administrador -->
                <?php
                $timezone = new DateTimeZone('America/Caracas');
                $server_now = new DateTime('now', $timezone);
                ?>
                <span style="color:#64748b; font-size:0.9rem; font-weight:500; display:flex; align-items:center; gap:5px;">
                    <span class="dashicons dashicons-time" style="font-size:18px; width:18px; height:18px;"></span>
                    Hora de Venezuela (Caracas): <strong><?php echo $server_now->format('d/m/Y H:i:s'); ?></strong>
                </span>
            </div>
        </form>
    </div>

    <!-- Script Inline del Cargador de Medios -->
    <script type="text/javascript">
    jQuery(document).ready(function($){
        $('.pro-upload-btn').click(function(e) {
            e.preventDefault();
            var button = $(this);
            var input_id = button.data('input-id');
            var preview_id = button.data('preview-id');
            
            var uploader = wp.media({
                title: 'Seleccionar Imagen de Portada',
                button: {
                    text: 'Usar Imagen Seleccionada'
                },
                multiple: false
            }).on('select', function() {
                var attachment = uploader.state().get('selection').first().toJSON();
                $('#' + input_id).val(attachment.url);
                $('#' + preview_id).html('<img src="' + attachment.url + '" style="max-height: 250px; width: 100%; object-fit: contain; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">');
                button.next('.pro-remove-btn').show();
            }).open();
        });
        
        $('.pro-remove-btn').click(function(e) {
            e.preventDefault();
            var button = $(this);
            var input_id = button.data('input-id');
            var preview_id = button.data('preview-id');
            $('#' + input_id).val('');
            $('#' + preview_id).html('<div style="background:#f1f5f9; padding:40px 20px; border-radius:8px; color:#94a3b8; text-align:center; border:2px dashed #cbd5e1;">Sin imagen seleccionada</div>');
            button.hide();
        });
    });
    </script>
    <?php
}
