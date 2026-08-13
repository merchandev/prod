<?php
namespace SSIVO_SEO\Includes;

class Metabox {
    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'register_meta_box' ] );
        add_action( 'save_post', [ $this, 'save_meta_box_data' ] );
    }

    public function register_meta_box() {
        add_meta_box(
            'ssivo_seo_snippet_preview',
            'SSIVO-SEO: Vista Previa y Configuración',
            [ $this, 'render_meta_box' ],
            ['post', 'page'],
            'normal',
            'high'
        );
    }

    public function render_meta_box( $post ) {
        // Añadir nonces para seguridad
        wp_nonce_field( 'ssivo_seo_save_data', 'ssivo_seo_meta_box_nonce' );

        // Obtener valores actuales (si existen)
        $custom_title = get_post_meta( $post->ID, '_ssivo_seo_custom_title', true );
        $custom_desc  = get_post_meta( $post->ID, '_ssivo_seo_custom_desc', true );

        // Pasamos estos valores iniciales al DOM para que React los lea
        ?>
        <div 
            id="ssivo-seo-metabox-root" 
            data-custom-title="<?php echo esc_attr( $custom_title ); ?>"
            data-custom-desc="<?php echo esc_attr( $custom_desc ); ?>"
        >
            <p>Cargando previsualizador interactivo de SEO...</p>
        </div>
        <?php
    }

    public function save_meta_box_data( $post_id ) {
        // 1. Verificación de seguridad
        if ( ! isset( $_POST['ssivo_seo_meta_box_nonce'] ) ) {
            return;
        }
        if ( ! wp_verify_nonce( $_POST['ssivo_seo_meta_box_nonce'], 'ssivo_seo_save_data' ) ) {
            return;
        }

        // 2. Verificar auto-guardado
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // 3. Verificar permisos
        if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

        // 4. Guardar datos personalizados
        if ( isset( $_POST['ssivo_seo_custom_title'] ) ) {
            update_post_meta( $post_id, '_ssivo_seo_custom_title', sanitize_text_field( wp_unslash( $_POST['ssivo_seo_custom_title'] ) ) );
        }
        
        if ( isset( $_POST['ssivo_seo_custom_desc'] ) ) {
            // Permitimos áreas de texto sin etiquetas HTML
            update_post_meta( $post_id, '_ssivo_seo_custom_desc', sanitize_textarea_field( wp_unslash( $_POST['ssivo_seo_custom_desc'] ) ) );
        }
    }
}
