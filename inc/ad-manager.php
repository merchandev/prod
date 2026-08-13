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
 * Gestor de Publicidad (Ad Manager)
 *
 * Registra los CPTs para los anuncios, meta boxes y funciones auxiliares.
 *
 * @package Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================================================
// 1. REGISTRAR CUSTOM POST TYPES
// ============================================================================
function pro_register_ad_cpts() {
    // 1.1 Banners de Inicio (Menú principal)
    register_post_type( 'pro_ad_banner', array(
        'labels'        => array(
            'name'          => 'Publicidad',
            'singular_name' => 'Banner Inicio',
            'add_new'       => 'Añadir Banner Inicio',
            'add_new_item'  => 'Añadir Nuevo Banner Inicio',
            'edit_item'     => 'Editar Banner Inicio',
            'all_items'     => 'Banners Inicio',
        ),
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'supports'      => array( 'title', 'thumbnail' ),
        'menu_icon'     => 'dashicons-megaphone',
        'menu_position' => 25,
    ) );

    // 1.2 Banners de Categorías (Submenú de Publicidad)
    register_post_type( 'pro_cat_banner', array(
        'labels'        => array(
            'name'          => 'Banners Categorías',
            'singular_name' => 'Banner Categoría',
            'add_new'       => 'Añadir Banner de Categoría',
            'add_new_item'  => 'Añadir Nuevo Banner de Categoría',
            'edit_item'     => 'Editar Banner de Categoría',
            'all_items'     => 'Banners Categorías',
        ),
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'edit.php?post_type=pro_ad_banner', // Aparece como submenú
        'supports'      => array( 'title', 'thumbnail' ),
    ) );
}
add_action( 'init', 'pro_register_ad_cpts' );

// Encolar media uploader en admin
function pro_enqueue_ad_admin_scripts( $hook ) {
    global $post;
    if ( ( $hook === 'post-new.php' || $hook === 'post.php' ) && $post && in_array( $post->post_type, array('pro_ad_banner', 'pro_cat_banner') ) ) {
        wp_enqueue_media();
    }
}
add_action( 'admin_enqueue_scripts', 'pro_enqueue_ad_admin_scripts' );

// ESTILOS COMPARTIDOS ADMIN
function pro_ad_admin_styles() {
    ?>
    <style>
        .pro-ad-meta-box { background: #f9fafb; border: 1px solid #dde1e7; border-radius: 6px; padding: 16px 18px; margin-bottom: 16px; }
        .pro-ad-meta-box p.title { font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #6b7280; margin: 0 0 14px 0; padding-bottom: 8px; border-bottom: 2px solid #e5e7eb; display: flex; align-items: center; gap: 7px; }
        .pro-field { margin-bottom: 12px; }
        .pro-field:last-child { margin-bottom: 0; }
        .pro-field label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.85rem; }
        .pro-field small { display: block; color: #6b7280; margin-top: 4px; font-size: 0.78rem; }
        .pro-field input[type="text"], .pro-field input[type="url"], .pro-field input[type="datetime-local"], .pro-field select { width: 100%; }
        .pro-slide-row { display: flex; gap: 8px; align-items: center; margin-top: 8px; }
        .pro-slide-row input { flex: 1; }
        
        #pro-cat-checklist {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 6px 14px; max-height: 220px;
            overflow-y: auto; padding: 10px 12px; background: #fff; border: 1px solid #bcd4f5; border-radius: 5px; margin-top: 8px;
        }
        #pro-cat-checklist label { display: flex; align-items: center; gap: 7px; font-size: 0.84rem; font-weight: 500; cursor: pointer; padding: 3px 0; }
        #pro-cat-checklist input[type="checkbox"] { margin: 0; flex-shrink: 0; }
        .pro-cat-select-btns { display: flex; gap: 8px; margin-top: 8px; }
        .pro-cat-select-btns button { font-size: 0.75rem; padding: 3px 10px; }
        
        .pro-slide-box { background: #fff; border: 1px solid #dde1e7; border-radius: 5px; padding: 12px 14px; margin-bottom: 10px; }
        .pro-slide-box strong { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: #374151; }
    </style>
    <script>
    jQuery(document).ready(function($) {
        $(document).on('click', '.pro-upload-btn', function(e) {
            e.preventDefault();
            var targetInput = $($(this).data('target'));
            var uploader = wp.media({ title: 'Seleccionar Imagen', button: { text: 'Usar esta imagen' }, multiple: false });
            uploader.on('select', function() { targetInput.val(uploader.state().get('selection').first().toJSON().url); });
            uploader.open();
        });
        
        $('input[name="pro_cat_scope"]').on('change', function() {
            if ($(this).val() === 'specific') { $('#pro-cat-specific-wrap').slideDown(200); } else { $('#pro-cat-specific-wrap').slideUp(200); }
        });
        $('#pro-cat-select-all').on('click', function(e) { e.preventDefault(); $('#pro-cat-checklist input[type="checkbox"]').prop('checked', true); });
        $('#pro-cat-deselect-all').on('click', function(e) { e.preventDefault(); $('#pro-cat-checklist input[type="checkbox"]').prop('checked', false); });
    });
    </script>
    <?php
}
add_action( 'admin_head', 'pro_ad_admin_styles' );

// ============================================================================
// 2. META BOXES PARA BANNER INICIO (pro_ad_banner)
// ============================================================================
function pro_add_ad_meta_boxes() {
    add_meta_box( 'pro_ad_settings', '⚙️ Configuración General', 'pro_ad_meta_box_html', 'pro_ad_banner', 'normal', 'high' );
    add_meta_box( 'pro_ad_slides', '🖼️ Slides Adicionales (Opcional)', 'pro_ad_slides_meta_box_html', 'pro_ad_banner', 'normal', 'high' );
    
    add_meta_box( 'pro_cat_settings', '⚙️ Configuración del Banner de Categoría', 'pro_cat_meta_box_html', 'pro_cat_banner', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'pro_add_ad_meta_boxes' );

// HTML: Configuración General (Inicio)
function pro_ad_meta_box_html( $post ) {
    wp_nonce_field( 'pro_save_ad_meta', 'pro_ad_meta_nonce' );

    $location = get_post_meta( $post->ID, '_pro_ad_location', true );
    $url      = get_post_meta( $post->ID, '_pro_ad_url', true );
    $start    = get_post_meta( $post->ID, '_pro_ad_start', true );
    $end      = get_post_meta( $post->ID, '_pro_ad_end', true );
    ?>
    <div class="pro-ad-meta-box">
        <p class="title">Ubicación y Enlace</p>
        <div class="pro-field">
            <label for="pro_ad_location">Ubicación en el Inicio:</label>
            <select name="pro_ad_location" id="pro_ad_location">
                <option value="header"          <?php selected( $location, 'header' ); ?>>Header (Arriba de todo)</option>
                <option value="below-hero"      <?php selected( $location, 'below-hero' ); ?>>Debajo del Hero (Noticias Principales)</option>
                <option value="in-feed-1"       <?php selected( $location, 'in-feed-1' ); ?>>Después de Sucesos (In-Feed 1)</option>
                <option value="in-feed-2"       <?php selected( $location, 'in-feed-2' ); ?>>Después de Locales (In-Feed 2)</option>
                <option value="nacional-ad"     <?php selected( $location, 'nacional-ad' ); ?>>Después de Nacional</option>
                <option value="internacional-ad"<?php selected( $location, 'internacional-ad' ); ?>>Después de Internacional</option>
                <option value="economia-ad"     <?php selected( $location, 'economia-ad' ); ?>>Después de Economía</option>
                <option value="seguridad-ad"    <?php selected( $location, 'seguridad-ad' ); ?>>Después de Seguridad</option>
                <option value="zona-deportiva-ad"<?php selected( $location, 'zona-deportiva-ad' ); ?>>Después de Zona deportiva</option>
            </select>
        </div>
        <div class="pro-field">
            <label for="pro_ad_url">URL de Destino del Slide 1 (Opcional):</label>
            <input type="url" name="pro_ad_url" id="pro_ad_url" value="<?php echo esc_url( $url ); ?>">
        </div>
    </div>
    
    <div class="pro-ad-meta-box">
        <p class="title">Duración de la Campaña</p>
        <div class="pro-field" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div>
                <label for="pro_ad_start">Fecha y Hora de Inicio:</label>
                <input type="datetime-local" name="pro_ad_start" id="pro_ad_start" value="<?php echo esc_attr( $start ); ?>">
                <small>Vacío = publicar ya.</small>
            </div>
            <div>
                <label for="pro_ad_end">Fecha y Hora de Caducidad:</label>
                <input type="datetime-local" name="pro_ad_end" id="pro_ad_end" value="<?php echo esc_attr( $end ); ?>">
                <small>Vacío = sin caducidad.</small>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var isSubmitting = false;
        $('#publish').on('click', function(e) {
            if (isSubmitting) return true;
            if ($(this).attr('id') !== 'publish' && $(this).attr('name') !== 'publish') return true;

            var location = $('#pro_ad_location').val();
            var post_id  = $('#post_ID').val() || 0;
            var nonce    = $('#pro_ad_meta_nonce').val();

            if (location) {
                e.preventDefault();
                $('#publishing-action .spinner').addClass('is-active');
                $(this).addClass('disabled');

                $.post(ajaxurl, {
                    action:   'pro_check_ad_location',
                    location: location,
                    post_id:  post_id,
                    nonce:    nonce
                }, function(response) {
                    $('#publishing-action .spinner').removeClass('is-active');
                    $('#publish').removeClass('disabled');
                    if (response.success && response.data.conflict) {
                        var msg = "⚠️ ATENCIÓN:\n\nYa hay un banner activo ('" + response.data.title + "') en esta ubicación.\n\nSi continúas, el anterior pasará a Borrador.\n\n¿Deseas reemplazarlo?";
                        if (confirm(msg)) { isSubmitting = true; $('#publish').click(); }
                    } else {
                        isSubmitting = true; $('#publish').click();
                    }
                }).fail(function() { isSubmitting = true; $('#publish').click(); });
            }
        });
    });
    </script>
    <?php
}

// HTML: Slides Adicionales (Inicio)
function pro_ad_slides_meta_box_html( $post ) {
    ?>
    <p style="font-size:0.83rem; color:#6b7280; margin-top:-6px; margin-bottom:14px;">
        La <strong>Imagen Destacada</strong> es tu Slide 1. Aquí puedes agregar hasta 9 slides más que rotarán automáticamente en el inicio.
    </p>

    <?php for ( $i = 2; $i <= 10; $i++ ) :
        $img_val = get_post_meta( $post->ID, '_pro_ad_image_' . $i, true );
        $url_val = get_post_meta( $post->ID, '_pro_ad_url_' . $i,   true );
    ?>
    <div class="pro-slide-box">
        <strong>Slide <?php echo esc_html( $i ); ?></strong>
        <div class="pro-field" style="margin-top:10px;">
            <label>Imagen:</label>
            <div class="pro-slide-row">
                <input type="text"
                       name="pro_ad_image_<?php echo $i; ?>"
                       id="pro_ad_image_<?php echo $i; ?>"
                       value="<?php echo esc_attr( $img_val ); ?>"
                       placeholder="URL de la imagen...">
                <button type="button" class="button pro-upload-btn" data-target="#pro_ad_image_<?php echo $i; ?>">📁 Subir</button>
            </div>
        </div>
        <div class="pro-field">
            <label>URL de Destino:</label>
            <input type="url" name="pro_ad_url_<?php echo $i; ?>" value="<?php echo esc_attr( $url_val ); ?>" placeholder="https://...">
        </div>
    </div>
    <?php endfor;
}

// ============================================================================
// 3. META BOXES PARA BANNER CATEGORÍAS (pro_cat_banner)
// ============================================================================
function pro_cat_meta_box_html( $post ) {
    wp_nonce_field( 'pro_save_cat_meta', 'pro_cat_meta_nonce' );

    $url_page_id  = get_post_meta( $post->ID, '_pro_cat_url_page_id', true );
    $start        = get_post_meta( $post->ID, '_pro_cat_start', true );
    $end          = get_post_meta( $post->ID, '_pro_cat_end', true );
    $sponsor_name = get_post_meta( $post->ID, '_pro_cat_sponsor_name', true );
    $sponsor_logo = get_post_meta( $post->ID, '_pro_cat_sponsor_logo', true );
    
    $cat_scope    = get_post_meta( $post->ID, '_pro_cat_scope', true ) ?: 'all';
    $cat_ids      = get_post_meta( $post->ID, '_pro_cat_ids', true );
    if ( ! is_array( $cat_ids ) ) $cat_ids = array();
    
    $page_ids     = get_post_meta( $post->ID, '_pro_cat_page_ids', true );
    if ( ! is_array( $page_ids ) ) $page_ids = array();

    $all_categories = get_terms( array(
        'taxonomy'   => 'category',
        'hide_empty' => false,
        'exclude'    => array( get_cat_ID( 'relevantes' ), get_cat_ID( 'Uncategorized' ) ),
        'orderby'    => 'name',
        'order'      => 'ASC',
    ) );
    
    $all_pages = get_pages( array(
        'sort_column'  => 'post_title',
        'sort_order'   => 'ASC',
        'post_status'  => 'publish'
    ) );
    ?>
    
    <div class="pro-ad-meta-box">
        <p class="title">Destino del Banner</p>
        <div class="pro-field">
            <label for="pro_cat_url_page_id">Página de Destino:</label>
            <?php
            wp_dropdown_pages( array(
                'name'              => 'pro_cat_url_page_id',
                'id'                => 'pro_cat_url_page_id',
                'show_option_none'  => '— Selecciona una página de destino —',
                'option_none_value' => '',
                'selected'          => $url_page_id,
            ) );
            ?>
            <small>Al hacer clic en el banner, el usuario será redirigido a esta página interna.</small>
        </div>
    </div>

    <div class="pro-ad-meta-box" style="background:#fdfcf0; border-color:#fef08a;">
        <p class="title" style="color:#a16207; border-color:#fef08a;">🖼️ Imágenes del Patrocinador (Hasta 10 Slides)</p>
        <p style="font-size: 0.8rem; color: #666; margin-top: 0;">Sube las imágenes del banner. Si subes más de una, se mostrarán como un carrusel que rota automáticamente.</p>
        <?php for ( $i = 1; $i <= 10; $i++ ) : 
            $slide_img = get_post_meta( $post->ID, '_pro_cat_slide_' . $i, true );
        ?>
            <div class="pro-field" style="margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px dashed #e5e7eb;">
                <label style="display:inline-block; width:60px; font-size:0.8rem;">Slide <?php echo $i; ?>:</label>
                <div class="pro-slide-row" style="display:inline-flex; width: calc(100% - 65px); margin-top:0;">
                    <input type="text" name="pro_cat_slide_<?php echo $i; ?>" id="pro_cat_slide_<?php echo $i; ?>" value="<?php echo esc_attr( $slide_img ); ?>" placeholder="https://...">
                    <button type="button" class="button pro-upload-btn" data-target="#pro_cat_slide_<?php echo $i; ?>">📁 Imagen</button>
                </div>
            </div>
        <?php endfor; ?>
    </div>



    <div class="pro-ad-meta-box" style="background:#eef6ff; border-color:#bcd4f5;">
        <p class="title" style="color:#1d4ed8; border-color:#bcd4f5;">🗂️ ¿En qué Páginas aparece este banner?</p>
        <div class="pro-field" style="display: flex; gap: 20px; margin-bottom: 14px;">
            <label style="font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <input type="radio" name="pro_cat_scope" value="all" <?php checked( $cat_scope, 'all' ); ?>>
                🌐 Mostrar en Todas (Global)
            </label>
            <label style="font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <input type="radio" name="pro_cat_scope" value="specific" <?php checked( $cat_scope, 'specific' ); ?>>
                🎯 Elegir Específicas
            </label>
        </div>
        
        <div id="pro-cat-specific-wrap" style="<?php echo ( $cat_scope === 'specific' ) ? '' : 'display:none;'; ?>">
            <p style="font-size: 0.85rem; font-weight: 600; margin-bottom: 5px; color: #4b5563;">📄 Páginas</p>
            <div id="pro-cat-checklist" style="border-color: #e5e7eb;">
                <?php if ( ! empty( $all_pages ) ) : ?>
                    <?php foreach ( $all_pages as $p ) : ?>
                        <label>
                            <input type="checkbox" name="pro_cat_page_ids[]" value="<?php echo esc_attr( $p->ID ); ?>" <?php checked( in_array( (string) $p->ID, array_map( 'strval', $page_ids ) ) ); ?>>
                            <?php echo esc_html( $p->post_title ); ?>
                        </label>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p style="color:#6b7280; font-size:0.85rem;">No se encontraron páginas.</p>
                <?php endif; ?>
            </div>
            <small style="margin-top:6px; display:block;">ℹ️ Si una página no tiene un banner específico asignado, usará el banner marcado como "Global".</small>
        </div>
    </div>
    
    <div class="pro-ad-meta-box">
        <p class="title">Duración de la Campaña</p>
        <div class="pro-field" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div>
                <label for="pro_cat_start">Fecha y Hora de Inicio:</label>
                <input type="datetime-local" name="pro_cat_start" id="pro_cat_start" value="<?php echo esc_attr( $start ); ?>">
            </div>
            <div>
                <label for="pro_cat_end">Fecha y Hora de Caducidad:</label>
                <input type="datetime-local" name="pro_cat_end" id="pro_cat_end" value="<?php echo esc_attr( $end ); ?>">
            </div>
        </div>
    </div>
    <?php
}

// ============================================================================
// 4. GUARDAR METADATOS
// ============================================================================
// Guardar Banners de Inicio
function pro_save_ad_meta( $post_id ) {
    if ( ! isset( $_POST['pro_ad_meta_nonce'] ) || ! wp_verify_nonce( $_POST['pro_ad_meta_nonce'], 'pro_save_ad_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['pro_ad_location'] ) ) update_post_meta( $post_id, '_pro_ad_location', sanitize_text_field( $_POST['pro_ad_location'] ) );
    if ( isset( $_POST['pro_ad_url'] ) ) update_post_meta( $post_id, '_pro_ad_url', esc_url_raw( $_POST['pro_ad_url'] ) );
    if ( isset( $_POST['pro_ad_start'] ) ) update_post_meta( $post_id, '_pro_ad_start', sanitize_text_field( $_POST['pro_ad_start'] ) );
    if ( isset( $_POST['pro_ad_end'] ) ) update_post_meta( $post_id, '_pro_ad_end', sanitize_text_field( $_POST['pro_ad_end'] ) );

    for ( $i = 2; $i <= 10; $i++ ) {
        if ( isset( $_POST[ 'pro_ad_image_' . $i ] ) ) update_post_meta( $post_id, '_pro_ad_image_' . $i, esc_url_raw( $_POST[ 'pro_ad_image_' . $i ] ) );
        if ( isset( $_POST[ 'pro_ad_url_' . $i ] ) ) update_post_meta( $post_id, '_pro_ad_url_' . $i, esc_url_raw( $_POST[ 'pro_ad_url_' . $i ] ) );
    }

    $new_location = get_post_meta( $post_id, '_pro_ad_location', true );
    if ( get_post_status( $post_id ) === 'publish' && $new_location ) {
        $conflict_query = new WP_Query( array(
            'post_type' => 'pro_ad_banner', 'post_status' => 'publish', 'post__not_in' => array( $post_id ),
            'meta_query' => array( array( 'key' => '_pro_ad_location', 'value' => $new_location ) ),
        ) );
        if ( $conflict_query->have_posts() ) {
            remove_action( 'save_post_pro_ad_banner', 'pro_save_ad_meta' );
            foreach ( $conflict_query->posts as $other ) { wp_update_post( array( 'ID' => $other->ID, 'post_status' => 'draft' ) ); }
            add_action( 'save_post_pro_ad_banner', 'pro_save_ad_meta' );
        }
    }
}
add_action( 'save_post_pro_ad_banner', 'pro_save_ad_meta' );

// Guardar Banners de Categorías
function pro_save_cat_meta( $post_id ) {
    if ( ! isset( $_POST['pro_cat_meta_nonce'] ) || ! wp_verify_nonce( $_POST['pro_cat_meta_nonce'], 'pro_save_cat_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['pro_cat_url_page_id'] ) ) update_post_meta( $post_id, '_pro_cat_url_page_id', intval( $_POST['pro_cat_url_page_id'] ) );
    if ( isset( $_POST['pro_cat_start'] ) ) update_post_meta( $post_id, '_pro_cat_start', sanitize_text_field( $_POST['pro_cat_start'] ) );
    if ( isset( $_POST['pro_cat_end'] ) ) update_post_meta( $post_id, '_pro_cat_end', sanitize_text_field( $_POST['pro_cat_end'] ) );


    for ( $i = 1; $i <= 10; $i++ ) {
        if ( isset( $_POST[ 'pro_cat_slide_' . $i ] ) ) update_post_meta( $post_id, '_pro_cat_slide_' . $i, esc_url_raw( $_POST[ 'pro_cat_slide_' . $i ] ) );
    }

    $cat_scope = isset( $_POST['pro_cat_scope'] ) ? sanitize_text_field( $_POST['pro_cat_scope'] ) : 'all';
    update_post_meta( $post_id, '_pro_cat_scope', $cat_scope );

    if ( $cat_scope === 'specific' ) {
        if ( isset( $_POST['pro_cat_ids'] ) && is_array( $_POST['pro_cat_ids'] ) ) {
            update_post_meta( $post_id, '_pro_cat_ids', array_map( 'intval', $_POST['pro_cat_ids'] ) );
        } else {
            update_post_meta( $post_id, '_pro_cat_ids', array() );
        }
        
        if ( isset( $_POST['pro_cat_page_ids'] ) && is_array( $_POST['pro_cat_page_ids'] ) ) {
            update_post_meta( $post_id, '_pro_cat_page_ids', array_map( 'intval', $_POST['pro_cat_page_ids'] ) );
        } else {
            update_post_meta( $post_id, '_pro_cat_page_ids', array() );
        }
    } else {
        update_post_meta( $post_id, '_pro_cat_ids', array() );
        update_post_meta( $post_id, '_pro_cat_page_ids', array() );
    }
}
add_action( 'save_post_pro_cat_banner', 'pro_save_cat_meta' );


// ============================================================================
// 5. HELPER: Recuperar anuncios activos (Banners Inicio)
// ============================================================================
function pro_get_active_ads( $location ) {
    $now = current_time( 'mysql' );
    $active_ads = array();

    $query = new WP_Query( array(
        'post_type' => 'pro_ad_banner', 'posts_per_page' => -1, 'post_status' => 'publish',
        'meta_query' => array( array( 'key' => '_pro_ad_location', 'value' => $location ) ),
    ) );

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $pid = get_the_ID();
            $start = get_post_meta( $pid, '_pro_ad_start', true );
            $end   = get_post_meta( $pid, '_pro_ad_end', true );

            if ( (!empty($start) && str_replace('T', ' ', $start) > $now) || (!empty($end) && str_replace('T', ' ', $end) <= $now) ) continue;

            $base_title = get_the_title();
            if ( has_post_thumbnail() ) {
                $active_ads[] = array(
                    'title' => $base_title, 'image' => get_the_post_thumbnail_url( $pid, 'full' ), 'url' => get_post_meta( $pid, '_pro_ad_url', true )
                );
            }
            for ( $i = 2; $i <= 5; $i++ ) {
                $img = get_post_meta( $pid, '_pro_ad_image_' . $i, true );
                if ( ! empty( $img ) ) {
                    $active_ads[] = array(
                        'title' => $base_title . ' - Slide ' . $i, 'image' => $img, 'url' => get_post_meta( $pid, '_pro_ad_url_' . $i, true )
                    );
                }
            }
        }
        wp_reset_postdata();
    }
    return $active_ads;
}

// ============================================================================
// 6. HELPER: Recuperar banner de categoría (Banners Categorías)
// ============================================================================
function pro_get_category_banner( $obj_id, $obj_type = 'category' ) {
    $now = current_time( 'mysql' );
    $query = new WP_Query( array(
        'post_type' => 'pro_cat_banner', 'posts_per_page' => -1, 'post_status' => 'publish',
    ) );

    $specific_match = null;
    $global_match   = null;

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $pid = get_the_ID();
            $start = get_post_meta( $pid, '_pro_cat_start', true );
            $end   = get_post_meta( $pid, '_pro_cat_end', true );

            if ( (!empty($start) && str_replace('T', ' ', $start) > $now) || (!empty($end) && str_replace('T', ' ', $end) <= $now) ) continue;
            
            // Check if it has at least one slide image or a thumbnail
            $has_image = has_post_thumbnail();
            for ( $i = 1; $i <= 10; $i++ ) {
                if ( !empty(get_post_meta( $pid, '_pro_cat_slide_' . $i, true )) ) {
                    $has_image = true;
                    break;
                }
            }
            if ( ! $has_image ) continue;

            $scope = get_post_meta( $pid, '_pro_cat_scope', true ) ?: 'all';
            
            if ( $scope === 'specific' ) {
                if ( $obj_type === 'category' ) {
                    $cat_ids = get_post_meta( $pid, '_pro_cat_ids', true );
                    if ( ! is_array( $cat_ids ) ) $cat_ids = array();
                    if ( in_array( (int) $obj_id, array_map( 'intval', $cat_ids ) ) && ! $specific_match ) $specific_match = $pid;
                } elseif ( $obj_type === 'page' ) {
                    $page_ids = get_post_meta( $pid, '_pro_cat_page_ids', true );
                    if ( ! is_array( $page_ids ) ) $page_ids = array();
                    if ( in_array( (int) $obj_id, array_map( 'intval', $page_ids ) ) && ! $specific_match ) $specific_match = $pid;
                }
            } else {
                if ( ! $global_match ) $global_match = $pid;
            }
        }
        wp_reset_postdata();
    }

    $matched_id = $specific_match ? $specific_match : $global_match;
    if ( ! $matched_id ) return null;

    $url_page_id = get_post_meta( $matched_id, '_pro_cat_url_page_id', true );
    $url = !empty($url_page_id) ? get_permalink($url_page_id) : '#';

    $slides = array();
    for ( $i = 1; $i <= 10; $i++ ) {
        $img = get_post_meta( $matched_id, '_pro_cat_slide_' . $i, true );
        if ( !empty($img) ) {
            $slides[] = $img;
        }
    }
    // Fallback al thumbnail si no hay slides
    if ( empty($slides) && has_post_thumbnail($matched_id) ) {
        $slides[] = get_the_post_thumbnail_url( $matched_id, 'full' );
    }

    return array(
        'title'        => get_the_title( $matched_id ),
        'slides'       => $slides,
        'url'          => $url,
        'sponsor_name' => get_post_meta( $matched_id, '_pro_cat_sponsor_name', true ),
        'sponsor_logo' => get_post_meta( $matched_id, '_pro_cat_sponsor_logo', true ),
    );
}

// ============================================================================
// 7. AJAX: Validar conflicto (Solo Banners de Inicio)
// ============================================================================
function pro_check_ad_location_ajax() {
    check_ajax_referer( 'pro_save_ad_meta', 'nonce' );
    $location = isset( $_POST['location'] ) ? sanitize_text_field( $_POST['location'] ) : '';
    $post_id  = isset( $_POST['post_id'] )  ? intval( $_POST['post_id'] )  : 0;
    if ( ! $location ) wp_send_json_error();

    $query = new WP_Query( array(
        'post_type' => 'pro_ad_banner', 'post_status' => 'publish', 'post__not_in' => array( $post_id ),
        'meta_query' => array( array( 'key' => '_pro_ad_location', 'value' => $location ) ),
    ) );

    $has_conflict = false; $conflict_title = ''; $now = current_time( 'mysql' );
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $start = get_post_meta( get_the_ID(), '_pro_ad_start', true );
            $end   = get_post_meta( get_the_ID(), '_pro_ad_end', true );
            if ( (empty($start) || str_replace('T', ' ', $start) <= $now) && (empty($end) || str_replace('T', ' ', $end) > $now) ) {
                $has_conflict = true; $conflict_title = get_the_title(); break;
            }
        }
        wp_reset_postdata();
    }
    wp_send_json_success( array( 'conflict' => $has_conflict, 'title' => $conflict_title ) );
}
add_action( 'wp_ajax_pro_check_ad_location', 'pro_check_ad_location_ajax' );

// ============================================================================
// 8. COLUMNAS PERSONALIZADAS EN EL ADMIN
// ============================================================================
function pro_cat_banner_columns( $columns ) {
    $columns['status_location'] = 'Estado y Ubicación';
    return $columns;
}
add_filter( 'manage_pro_cat_banner_posts_columns', 'pro_cat_banner_columns' );

function pro_cat_banner_custom_column( $column, $post_id ) {
    if ( $column === 'status_location' ) {
        $now = current_time( 'mysql' );
        $start = get_post_meta( $post_id, '_pro_cat_start', true );
        $end   = get_post_meta( $post_id, '_pro_cat_end', true );
        
        $is_active = true;
        $status_text = '<span style="color: green; font-weight: bold;">Activo</span>';
        if ( get_post_status( $post_id ) !== 'publish' ) {
            $is_active = false;
            $status_text = '<span style="color: gray;">Borrador</span>';
        } elseif ( !empty($start) && str_replace('T', ' ', $start) > $now ) {
            $is_active = false;
            $status_text = '<span style="color: orange;">Programado</span>';
        } elseif ( !empty($end) && str_replace('T', ' ', $end) <= $now ) {
            $is_active = false;
            $status_text = '<span style="color: red;">Caduco</span>';
        }

        $images_count = 0;
        for ( $i = 1; $i <= 10; $i++ ) {
            if ( !empty(get_post_meta( $post_id, '_pro_cat_slide_' . $i, true )) ) {
                $images_count++;
            }
        }

        $scope = get_post_meta( $post_id, '_pro_cat_scope', true ) ?: 'all';
        $location_text = '<strong>Ubicación:</strong> Todas (Global)';
        if ( $scope === 'specific' ) {
            $cat_ids = get_post_meta( $post_id, '_pro_cat_ids', true );
            $page_ids = get_post_meta( $post_id, '_pro_cat_page_ids', true );
            $locs = array();
            
            if ( !empty($cat_ids) && is_array($cat_ids) ) {
                $locs[] = count($cat_ids) . ' Categorías';
            }
            if ( !empty($page_ids) && is_array($page_ids) ) {
                $locs[] = count($page_ids) . ' Páginas';
            }
            if ( empty($locs) ) {
                $location_text = '<strong>Ubicación:</strong> Ninguna seleccionada';
            } else {
                $location_text = '<strong>Ubicación:</strong> Específicas (' . implode(', ', $locs) . ')';
            }
        }

        echo '<div>' . $status_text . '</div>';
        echo '<div style="margin-top: 4px; font-size: 0.85em; color: #555;">' . $images_count . ' Slide(s) cargado(s)</div>';
        echo '<div style="margin-top: 4px; font-size: 0.85em; color: #555;">' . $location_text . '</div>';
    }
}
add_action( 'manage_pro_cat_banner_posts_custom_column', 'pro_cat_banner_custom_column', 10, 2 );
