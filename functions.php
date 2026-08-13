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
 * Funciones y definiciones del tema Edit-Pro
 * Framework editorial premium white-label/SaaS para diarios digitales e impresos.
 *
 * @package Edit-Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Salir si se accede directamente.
}

// Ocultar mensajes de funciones obsoletas (Deprecated) generados por plugins de terceros
add_filter( 'deprecated_function_trigger_error', '__return_false' );
add_filter( 'deprecated_file_trigger_error', '__return_false' );
add_filter( 'deprecated_argument_trigger_error', '__return_false' );
add_filter( 'deprecated_hook_trigger_error', '__return_false' );

// Forzar la hora local de Caracas / Venezuela de manera global en todo WordPress y el tema
add_filter( 'pre_option_timezone_string', function() {
    return 'America/Caracas';
} );
add_filter( 'pre_option_gmt_offset', function() {
    return -4;
} );

/**
 * Configuración inicial del tema.
 */
function pro_setup() {
    // Traducciones
    load_theme_textdomain( 'pro', get_template_directory() . '/languages' );

    // Título dinámico
    add_theme_support( 'title-tag' );

    // Soporte para imágenes destacadas
    add_theme_support( 'post-thumbnails' );

    // Tamaños de imágenes personalizados para mejor rendimiento
    // Héroe: Noticia principal (LCP)
    add_image_size( 'hero-thumbnail', 1200, 675, true ); 
    // Card: Noticias secundarias
    add_image_size( 'card-thumbnail', 600, 400, true ); 
    
    // Registrar menús de navegación
    register_nav_menus( array(
        'primary' => esc_html__( 'Menú Principal (PC)', 'pro' ),
        'mobile'  => esc_html__( 'Menú Móvil (Teléfonos)', 'pro' ),
        'footer'  => esc_html__( 'Menú del Pie de Página', 'pro' ),
        'topbar'  => esc_html__( 'Menú Superior', 'pro' ),
    ) );

    // Soporte HTML5
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    // Logo personalizado
    add_theme_support( 'custom-logo', array(
        'height'      => 300,
        'width'       => 1000,
        'flex-width'  => true,
        'flex-height' => true,
    ) );
}
add_action( 'after_setup_theme', 'pro_setup' );

function pro_add_preconnect() {
    // Preconnect a Google Fonts para mejorar LCP
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
}
add_action( 'wp_head', 'pro_add_preconnect', 1 );

/**
 * Encolar scripts y estilos.
 */
function pro_scripts() {
    // Fuentes de Google (Playfair Display, Source Serif 4, Inter)
    wp_enqueue_style( 'pro-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Source+Serif+4:ital,wght@0,400;0,600;1,400&display=swap', array(), null );

    // Iconos de Google (Material Symbols)
    wp_enqueue_style( 'pro-icons', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200', array(), null );

    // Normalize.css para consistencia entre navegadores
    wp_enqueue_style( 'pro-normalize', 'https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css', array(), '8.0.1' );

    // Estilos principales (CSS customizado)
    // Auditoría fix: file_exists() evita E_WARNING si el archivo no existe en producción.
    $css_path = get_template_directory() . '/assets/css/main.css';
    $css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : '1.0.0';
    wp_enqueue_style( 'pro-main-style', get_template_directory_uri() . '/assets/css/main.css', array(), $css_ver );

    // Estilos de WordPress (style.css para variables CSS)
    $style_path = get_stylesheet_directory() . '/style.css';
    $style_ver  = file_exists( $style_path ) ? filemtime( $style_path ) : '1.0.0';
    wp_enqueue_style( 'pro-style', get_stylesheet_uri(), array('pro-main-style'), $style_ver );

    // Script principal (Modo oscuro, progreso de lectura, scroll infinito)
    $js_path = get_template_directory() . '/assets/js/main.js';
    $js_ver  = file_exists( $js_path ) ? filemtime( $js_path ) : '1.0.0';
    wp_enqueue_script( 'pro-main-js', get_template_directory_uri() . '/assets/js/main.js', array(), $js_ver, true );

    // Script para buscador Ajax
    wp_enqueue_script( 'pro-ajax-search', get_template_directory_uri() . '/assets/js/ajax-search.js', array(), '1.0.0', true );
    
    // Generar Nonce seguro una sola vez por petición
    $ajax_nonce = wp_create_nonce( 'pro_ajax_nonce' );

    // Pasar URL de admin-ajax al script
    wp_localize_script( 'pro-ajax-search', 'pro_ajax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => $ajax_nonce
    ));

    global $wp_query;
    // Usar transient para evitar query extra en cada carga de página
    $latest_date = get_transient( 'pro_latest_post_date' );
    if ( false === $latest_date ) {
        $latest_post = get_posts( array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'fields'         => 'ids',
        ) );
        if ( ! empty( $latest_post ) ) {
            $latest_date = get_post_field( 'post_date', $latest_post[0] );
        } else {
            $latest_date = '';
        }
        set_transient( 'pro_latest_post_date', $latest_date, 60 ); // Cache 60 segundos
    }

    $cat_id = 0;
    if ( is_category() ) {
        $cat_id = get_queried_object_id();
    } elseif ( is_page_template( 'page-categoria.php' ) ) {
        // Usar slug de la página para detectar la categoría (más fiable que el título)
        $queried_page = get_queried_object();
        if ( $queried_page ) {
            $category = get_term_by( 'slug', $queried_page->post_name, 'category' );
            if ( ! $category ) {
                $category = get_term_by( 'name', $queried_page->post_title, 'category' );
            }
            if ( $category ) {
                $cat_id = $category->term_id;
            }
        }
    }

    wp_localize_script( 'pro-main-js', 'pro_loadmore_params', array(
        'ajax_url'     => admin_url( 'admin-ajax.php' ),
        'current_page' => get_query_var( 'paged' ) ? get_query_var('paged') : 1,
        'max_page'     => $wp_query->max_num_pages,
        'query_vars'   => json_encode( $wp_query->query_vars ),
        'category_id'  => $cat_id,
        'latest_date'  => $latest_date,
        'nonce'        => $ajax_nonce
    ));
}
add_action( 'wp_enqueue_scripts', 'pro_scripts' );

// =============================================================================
// HELPERS DE CATEGORÍA Y SISTEMA DE CACHÉ EDITORIAL
// Auditoría fix: invalidación completa al publicar o cambiar categorías.
// =============================================================================

/**
 * Resuelve el term_id de una categoría a partir de su slug.
 * Centraliza la resolución para evitar IDs hardcodeados en templates.
 *
 * @param string $slug Slug de la categoría.
 * @return int term_id o 0 si no existe.
 */
function eo_get_category_id( string $slug ): int {
    $term = get_category_by_slug( $slug );
    return ( $term instanceof WP_Term ) ? (int) $term->term_id : 0;
}

/**
 * Elimina todos los transients editoriales afectados por un post.
 * Sube por toda la jerarquía de categorías para invalidar padres también.
 *
 * @param int $post_id ID del post (0 = solo transients globales).
 */
function eo_clear_editorial_cache( int $post_id = 0 ): void {
    // Transients globales — siempre se limpian
    delete_transient( 'pro_latest_post_date' );
    delete_transient( 'pro_ticker_posts' );

    if ( $post_id <= 0 ) {
        return;
    }

    // Transients por categoría + ancestros
    $category_ids = wp_get_post_categories( $post_id, array( 'fields' => 'ids' ) );

    if ( empty( $category_ids ) ) {
        return;
    }

    $all_ids = array_map( 'intval', $category_ids );

    foreach ( $category_ids as $cat_id ) {
        $ancestors = get_ancestors( (int) $cat_id, 'category', 'taxonomy' );
        foreach ( $ancestors as $ancestor_id ) {
            $all_ids[] = (int) $ancestor_id;
        }
    }

    $all_ids = array_unique( $all_ids );

    foreach ( $all_ids as $tid ) {
        delete_transient( 'eo_category_' . $tid );
    }
}

/**
 * Hook: limpiar caché al publicar, actualizar o despublicar un post.
 * Cubre también cambios de estado (draft → publish, publish → trash).
 */
add_action( 'save_post_post', function ( int $post_id, WP_Post $post, bool $update ): void {
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }
    eo_clear_editorial_cache( $post_id );
}, 20, 3 );

/**
 * Hook: limpiar caché cuando se cambia la categoría de un post.
 * save_post_post no es suficiente: si el post ya está publicado y solo
 * se le cambia la categoría, set_object_terms dispara pero save_post no.
 */
add_action( 'set_object_terms', function (
    int    $object_id,
    array  $terms,
    array  $tt_ids,
    string $taxonomy,
    bool   $append,
    array  $old_tt_ids
): void {
    if ( 'category' !== $taxonomy ) {
        return;
    }
    // Solo limpiar si los términos realmente cambiaron
    if ( $tt_ids === $old_tt_ids ) {
        return;
    }
    eo_clear_editorial_cache( $object_id );
}, 20, 6 );

/**
 * Hook: transition_post_status — mantener compatibilidad con el ticker
 * y con futuros plugins que puedan depender de este hook.
 */
add_action( 'transition_post_status', function ( string $new_status, string $old_status, WP_Post $post ): void {
    if ( 'post' === $post->post_type && ( 'publish' === $new_status || 'publish' === $old_status ) ) {
        // Ya cubierto por save_post_post, pero lo dejamos para el ticker
        delete_transient( 'pro_latest_post_date' );
        delete_transient( 'pro_ticker_posts' );
    }
}, 10, 3 );

/**
 * pre_get_posts — Normalizar la consulta principal del archivo nativo de categorías.
 *
 * category.php usa have_posts() / the_post() de la consulta principal.
 * WordPress incluye hijos por defecto en is_category(), pero este hook
 * garantiza ordenado preciso, sin sticky posts y con 20 entradas.
 */
add_action( 'pre_get_posts', function ( WP_Query $query ): void {
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_category() ) {
        return;
    }

    $query->set( 'post_status',         'publish' );
    $query->set( 'posts_per_page',      20 );
    $query->set( 'orderby',             array( 'date' => 'DESC', 'ID' => 'DESC' ) );
    $query->set( 'ignore_sticky_posts', true );
} );


/**
 * Diferir scripts (Defer) para no bloquear el renderizado
 */
function pro_defer_scripts( $tag, $handle ) {
    // Si estamos en el admin, no modificamos
    if ( is_admin() ) {
        return $tag;
    }
    
    // Lista de scripts a diferir
    $defer_scripts = array( 'pro-main-js', 'pro-ajax-search' );
    
    if ( in_array( $handle, $defer_scripts ) ) {
        return str_replace( ' src', ' defer="defer" src', $tag );
    }
    
    return $tag;
}
add_filter( 'script_loader_tag', 'pro_defer_scripts', 10, 2 );

/**
 * Registrar áreas de widgets (Para publicidad y pie de página)
 */
function pro_widgets_init() {
    register_sidebar( array(
        'name'          => esc_html__( 'Sidebar Principal', 'pro' ),
        'id'            => 'sidebar-1',
        'description'   => esc_html__( 'Añade widgets aquí.', 'pro' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );
    
    // Espacio para publicidad en Header
    register_sidebar( array(
        'name'          => esc_html__( 'Anuncio Cabecera', 'pro' ),
        'id'            => 'ad-header',
        'description'   => esc_html__( 'Añade un banner publicitario de 728x90 aquí.', 'pro' ),
        'before_widget' => '<div class="ad-container ad-header">',
        'after_widget'  => '</div>',
    ) );

    // Espacios publicitarios in-feed
    register_sidebar( array(
        'name'          => esc_html__( 'Anuncio In-Feed', 'pro' ),
        'id'            => 'ad-in-feed',
        'description'   => esc_html__( 'Añade banners que aparecerán entre las noticias.', 'pro' ),
        'before_widget' => '<div class="ad-container ad-in-feed">',
        'after_widget'  => '</div>',
    ) );
}
add_action( 'widgets_init', 'pro_widgets_init' );

/**
 * Seguridad: Remover versión de WordPress de la cabecera
 */
remove_action('wp_head', 'wp_generator');

/**
 * Buscador Predictivo (Ajax Endpoint)
 */
function pro_ajax_search() {
    check_ajax_referer('pro_ajax_nonce', 'nonce');

    $search_query = isset($_POST['s']) ? sanitize_text_field($_POST['s']) : '';
    
    if($search_query) {
        $args = array(
            's' => $search_query,
            'posts_per_page' => 5,
            'post_status' => 'publish'
        );
        $query = new WP_Query($args);
        
        if($query->have_posts()) {
            echo '<ul class="ajax-search-results">';
            while($query->have_posts()) {
                $query->the_post();
                echo '<li><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></li>';
            }
            echo '</ul>';
        } else {
            echo '<p class="ajax-no-results">No se encontraron resultados.</p>';
        }
        wp_reset_postdata();
    }
    
    wp_die();
}
add_action('wp_ajax_nopriv_pro_ajax_search', 'pro_ajax_search');
add_action('wp_ajax_pro_ajax_search', 'pro_ajax_search');

/**
 * Registro de Custom Post Types y Taxonomías
 */
function pro_register_cpts() {
    // Clasificados se movió a inc/clasificados.php
}

/**
 * Sistema nativo de clasificados.
 */
$espressivo_clasificados_file =
    get_template_directory() . '/inc/clasificados.php';

if ( is_readable( $espressivo_clasificados_file ) ) {
    require_once $espressivo_clasificados_file;
}

// =============================================================================
// ROLES PERSONALIZADOS — Espressivo Venezuela
// Auditoría fix (v2): capacidades mínimas por rol.
// IMPORTANTE: WordPress guarda roles en la BD (wp_options → wp_user_roles).
// Los roles ya existentes NO se actualizan con add_role(). Por eso existe
// pro_migrate_roles_v2() que los corrige directamente en la BD una sola vez.
// =============================================================================

/**
 * Capacidades mínimas por rol editorial.
 * Ningún rol copia capabilities del administrador.
 */
function pro_add_espressivo_roles() {

    // --- Dirección: lectura y gestión editorial, sin configuración del sitio ---
    if ( ! get_role( 'direccion' ) ) {
        add_role( 'direccion', 'Dirección', array(
            'read'                        => true,
            'upload_files'                => true,
            'edit_posts'                  => true,
            'edit_others_posts'           => true,
            'edit_published_posts'        => true,
            'publish_posts'               => true,
            'delete_posts'                => true,
            'delete_others_posts'         => true,
            'delete_published_posts'      => true,
            'manage_categories'           => true,
            'moderate_comments'           => true,
            'export_own_editorial_report' => true,
        ) );
    }

    // --- Gerencia: gestión de contenido, medios, anuncios y carteles ---
    if ( ! get_role( 'gerencia' ) ) {
        add_role( 'gerencia', 'Gerencia', array(
            'read'                   => true,
            'edit_posts'             => true,
            'edit_others_posts'      => true,
            'edit_published_posts'   => true,
            'publish_posts'          => true,
            'delete_posts'           => true,
            'delete_others_posts'    => true,
            'delete_published_posts' => true,
            'upload_files'           => true,
            'manage_categories'      => true,
            'moderate_comments'      => true,
            // Capacidades propias del tema
            'manage_espressivo_ads'  => true,
            'view_contact_messages'  => true,
            'manage_carteles'        => true,
            'publish_carteles'       => true,
            'view_editorial_reports' => true,
        ) );
    }

    // --- Publicista: solo anuncios y subida de archivos ---
    if ( ! get_role( 'publicista' ) ) {
        add_role( 'publicista', 'Publicista', array(
            'read'                  => true,
            'upload_files'          => true,
            // Capacidades propias del tema
            'manage_espressivo_ads' => true,
        ) );
    }
}
add_action( 'init', 'pro_add_espressivo_roles' );

/**
 * Migración de roles existentes en la BD (v2).
 *
 * Los roles Dirección, Gerencia y Publicista fueron creados anteriormente
 * con las capacidades del administrador. Esta función retira esas capacidades
 * peligrosas de los roles ya almacenados en wp_user_roles y marca la migración
 * como completada para no repetirla.
 *
 * Ejecutar una sola vez al cargar admin_init.
 */
function pro_migrate_roles_v3() {
    // Asegurar que el administrador tenga la nueva capacidad de exportar
    $admin = get_role( 'administrator' );
    if ( $admin && ! $admin->has_cap( 'export_contact_messages' ) ) {
        $admin->add_cap( 'export_contact_messages' );
    }

    // Solo correr si la migración no se ha completado todavía
    if ( get_option( 'pro_roles_migrated_v3' ) ) {
        return;
    }

    $role = get_role( 'direccion' );
    if ( $role ) {
        $allowed = array(
            'read',
            'upload_files',
            'edit_posts',
            'edit_others_posts',
            'edit_published_posts',
            'publish_posts',
            'delete_posts',
            'delete_others_posts',
            'delete_published_posts',
            'manage_categories',
            'moderate_comments',
            'export_own_editorial_report',
        );

        foreach ( array_keys( $role->capabilities ) as $cap ) {
            $role->remove_cap( $cap );
        }

        foreach ( $allowed as $cap ) {
            $role->add_cap( $cap );
        }
    }

    // Asignar capacidades mínimas correctas al publicista por si tenía de más
    $publicista = get_role( 'publicista' );
    if ( $publicista ) {
        // Retirar todo y dejar solo lo necesario
        $all_caps = array_keys( $publicista->capabilities );
        $allowed  = array( 'read', 'upload_files', 'manage_espressivo_ads' );
        foreach ( $all_caps as $cap ) {
            if ( ! in_array( $cap, $allowed, true ) ) {
                $publicista->remove_cap( $cap );
            }
        }
    }

    // Marcar migración como completada
    update_option( 'pro_roles_migrated_v3', true );
}
add_action( 'admin_init', 'pro_migrate_roles_v3' );

/**
 * Protección real de pantallas administrativas.
 * Redirige a roles sin permiso que intenten acceder directamente.
 * Complementa (pero no reemplaza) la ocultación visual de menús.
 */
function pro_protect_admin_screens() {
    $user = wp_get_current_user();
    $restricted_roles = array( 'direccion', 'gerencia', 'publicista' );

    if ( ! array_intersect( $restricted_roles, (array) $user->roles ) ) {
        return; // No es un rol restringido
    }

    // Pantallas prohibidas para roles editoriales
    $blocked_pages = array(
        'plugins.php',
        'plugin-install.php',
        'plugin-editor.php',
        'themes.php',
        'theme-install.php',
        'theme-editor.php',
        'options-general.php',
        'options-writing.php',
        'options-reading.php',
        'options-discussion.php',
        'options-media.php',
        'options-permalink.php',
        'tools.php',
        'import.php',
        'export.php',
        'users.php',
        'user-new.php',
        'update-core.php',
    );

    $current_page = isset( $_SERVER['SCRIPT_NAME'] ) ? basename( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) ) : '';

    if ( in_array( $current_page, $blocked_pages, true ) ) {
        wp_die(
            esc_html__( 'No tienes permisos para acceder a esta página.', 'pro' ),
            esc_html__( 'Acceso denegado', 'pro' ),
            array( 'response' => 403, 'back_link' => true )
        );
    }
}
add_action( 'admin_init', 'pro_protect_admin_screens' );

/**
 * Ocultar entradas de menú (solo visual — la protección real está arriba).
 */
function pro_restrict_direccion_menus() {
    $current_user = wp_get_current_user();
    $restricted   = array( 'direccion', 'gerencia', 'publicista' );

    if ( ! array_intersect( $restricted, (array) $current_user->roles ) ) {
        return;
    }

    remove_menu_page( 'hostinger' );
    remove_menu_page( 'hostinger-reach' );
    remove_menu_page( 'toplevel_page_hostinger' );
    remove_menu_page( 'edit-comments.php' );
    remove_menu_page( 'themes.php' );
    remove_menu_page( 'plugins.php' );
    remove_menu_page( 'tools.php' );
    remove_menu_page( 'options-general.php' );
    remove_menu_page( 'users.php' );
    remove_menu_page( 'update-core.php' );
}
add_action( 'admin_menu', 'pro_restrict_direccion_menus', 999 );
add_action('init', 'pro_register_cpts');

/**
 * Paginación AJAX (Cargar más)
 */
function pro_load_more_posts() {
    check_ajax_referer('pro_ajax_nonce', 'nonce');

    // Seguridad Crítica: Reconstruir argumentos en el servidor
    $paged = isset($_POST['page']) ? intval($_POST['page']) + 1 : 1;
    $cat_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => get_option('posts_per_page'),
        'paged'          => $paged,
    );
    if ( $cat_id > 0 ) {
        $args['cat'] = $cat_id;
    }

    $query = new WP_Query( $args );

    if( $query->have_posts() ) :
        while( $query->have_posts() ): $query->the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('card-post'); ?>>
                <?php if ( has_post_thumbnail() ) : ?>
                    <a href="<?php the_permalink(); ?>" class="post-thumbnail" aria-hidden="true" tabindex="-1">
                        <?php the_post_thumbnail( 'card-thumbnail', array( 'loading' => 'lazy' ) ); ?>
                    </a>
                <?php endif; ?>
                <div class="card-content">
                    <div class="post-meta">
                        <?php pro_post_categories(); ?>
                        <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                    </div>
                    <h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                    <div class="entry-excerpt">
                        <?php echo esc_html( wp_strip_all_tags( wp_trim_words( get_the_excerpt(), 20, '...' ) ) ); ?>
                    </div>
                </div>
            </article>
            <?php
        endwhile;
    endif;
    wp_die();
}
add_action('wp_ajax_nopriv_pro_load_more_posts', 'pro_load_more_posts');
add_action('wp_ajax_pro_load_more_posts', 'pro_load_more_posts');

/**
 * Polling AJAX: Comprobar si hay nuevas noticias
 */
function pro_check_new_posts() {
    check_ajax_referer( 'pro_ajax_nonce', 'nonce' );

    $latest_date = isset( $_POST['latest_date'] ) ? sanitize_text_field( wp_unslash( $_POST['latest_date'] ) ) : '';

    // Validar que sea una fecha con formato MySQL válido antes de usarla en la query
    if ( ! empty( $latest_date ) && preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $latest_date ) ) {
        $args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'date_query'     => array(
                array(
                    'after'     => $latest_date,
                    'inclusive' => false,
                ),
            ),
        );
        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            wp_send_json_success( array( 'has_new_posts' => true ) );
        }
    }

    wp_send_json_success( array( 'has_new_posts' => false ) );
}
add_action('wp_ajax_nopriv_pro_check_new_posts', 'pro_check_new_posts');
add_action('wp_ajax_pro_check_new_posts', 'pro_check_new_posts');

/**
 * Función Helper: Renderizar categorías del post
 */
function pro_post_categories( $post_id = null, $force_category_slug = null ) {
    $categories = get_the_category( $post_id );
    if ( ! empty( $categories ) ) {
        $primary_category = null;
        $current_cat_id = 0;

        // Intentar detectar si estamos viendo una categoría específica
        if ( wp_doing_ajax() && isset($_POST['category_id']) && intval($_POST['category_id']) > 0 ) {
            $current_cat_id = intval($_POST['category_id']);
        } elseif ( is_category() ) {
            $current_cat_id = get_queried_object_id();
        } elseif ( is_page_template( 'page-categoria.php' ) ) {
            // Usar el slug de la página (más fiable que el título)
            $queried_page = get_post( get_queried_object_id() );
            if ( $queried_page ) {
                $page_slug     = $queried_page->post_name;
                $category_obj  = get_term_by( 'slug', $page_slug, 'category' );
                if ( ! $category_obj ) {
                    $category_obj = get_term_by( 'name', get_the_title( $queried_page->ID ), 'category' );
                }
                if ( $category_obj ) {
                    $current_cat_id = $category_obj->term_id;
                }
            }
        }

        // 0. Si se pide forzar una etiqueta mediante código (ej. Hero de Inicio o page-categoria)
        if ( $force_category_slug ) {
            // 0a. Buscar match exacto por slug entre las categorías del post
            foreach ( $categories as $cat ) {
                if ( $cat->slug === $force_category_slug ) {
                    $primary_category = $cat;
                    break;
                }
            }

            // 0b. Si el post está en una SUBcategoría de la categoría forzada,
            //     mostramos la categoría forzada (la de la página) para no confundir al lector.
            if ( ! $primary_category ) {
                $forced_term = get_term_by( 'slug', $force_category_slug, 'category' );
                if ( $forced_term ) {
                    foreach ( $categories as $cat ) {
                        $ancestors = get_ancestors( $cat->term_id, 'category' );
                        if ( in_array( $forced_term->term_id, $ancestors, true ) ) {
                            // El post está en una subcategoría — mostrar la categoría padre (de la página)
                            $primary_category = $forced_term;
                            break;
                        }
                    }
                }
            }
        }

        // 1. Si estamos en una categoría, forzar que la etiqueta muestre esa misma categoría
        if ( ! $primary_category && $current_cat_id > 0 ) {
            foreach ( $categories as $category ) {
                if ( $category->term_id === $current_cat_id ) {
                    $primary_category = $category;
                    break;
                }
            }
            // 1b. El post puede estar en una subcategoría de la categoría actual
            if ( ! $primary_category ) {
                $parent_term = get_term( $current_cat_id, 'category' );
                foreach ( $categories as $cat ) {
                    $ancestors = get_ancestors( $cat->term_id, 'category' );
                    if ( in_array( $current_cat_id, $ancestors, true ) ) {
                        $primary_category = $parent_term ? $parent_term : $cat;
                        break;
                    }
                }
            }
        }

        // 2. Fallback: evitar 'relevantes' y dar prioridad a subcategorías sobre 'sucesos'
        if ( ! $primary_category ) {
            // Intentar buscar una categoría que sea más específica (que no sea ni relevantes ni la general sucesos)
            foreach ( $categories as $category ) {
                if ( $category->slug !== 'relevantes' && $category->slug !== 'sucesos' ) {
                    $primary_category = $category;
                    break;
                }
            }
            // Si la única que tiene es sucesos o relevantes, usar la básica
            if ( ! $primary_category ) {
                $primary_category = $categories[0];
                foreach ( $categories as $category ) {
                    if ( $category->slug !== 'relevantes' ) {
                        $primary_category = $category;
                        break;
                    }
                }
            }
        }

        $cat_slug = $primary_category->slug;
        $cat_name = $primary_category->name;
        $cat_link = get_category_link( $primary_category->term_id );

        // Regla especial para Sucesos Monagas
        if ( $cat_slug === 'sucesos-monagas' ) {
            $cat_slug = 'sucesos';
            $cat_name = 'Monagas';
            $cat_link = home_url( '/category/sucesos/' );
        }

        echo '<span class="cat-label cat-' . esc_attr( $cat_slug ) . '">';
        echo '<a href="' . esc_url( $cat_link ) . '">';
        echo esc_html( $cat_name );
        echo '</a></span> ';
    }
}

/**
 * Personalizador (Customizer)
 */
function pro_customize_register( $wp_customize ) {
    // Sección de Redes Sociales
    $wp_customize->add_section( 'pro_social_settings', array(
        'title'    => esc_html__( 'Redes Sociales', 'pro' ),
        'priority' => 30,
    ) );
    
    // Facebook
    $wp_customize->add_setting( 'pro_social_facebook', array( 'default' => '#', 'sanitize_callback' => 'esc_url_raw' ) );
    $wp_customize->add_control( 'pro_social_facebook', array(
        'label'    => esc_html__( 'URL de Facebook', 'pro' ),
        'section'  => 'pro_social_settings',
        'type'     => 'url',
    ) );
    // Twitter
    $wp_customize->add_setting( 'pro_social_twitter', array( 'default' => '#', 'sanitize_callback' => 'esc_url_raw' ) );
    $wp_customize->add_control( 'pro_social_twitter', array(
        'label'    => esc_html__( 'URL de Twitter / X', 'pro' ),
        'section'  => 'pro_social_settings',
        'type'     => 'url',
    ) );
    // Instagram
    $wp_customize->add_setting( 'pro_social_instagram', array( 'default' => '#', 'sanitize_callback' => 'esc_url_raw' ) );
    $wp_customize->add_control( 'pro_social_instagram', array(
        'label'    => esc_html__( 'URL de Instagram', 'pro' ),
        'section'  => 'pro_social_settings',
        'type'     => 'url',
    ) );
    // Telegram
    $wp_customize->add_setting( 'pro_social_telegram', array( 'default' => '#', 'sanitize_callback' => 'esc_url_raw' ) );
    $wp_customize->add_control( 'pro_social_telegram', array(
        'label'    => esc_html__( 'URL de Telegram', 'pro' ),
        'section'  => 'pro_social_settings',
        'type'     => 'url',
    ) );

    // Sección de Publicidad
    $wp_customize->add_section( 'pro_ad_settings', array(
        'title'    => esc_html__( 'Publicidad', 'pro' ),
        'priority' => 35,
    ) );
    $wp_customize->add_setting( 'pro_show_ad_placeholders', array( 'default' => true, 'sanitize_callback' => 'rest_sanitize_boolean' ) );
    $wp_customize->add_control( 'pro_show_ad_placeholders', array(
        'label'    => esc_html__( 'Mostrar Placeholders vacíos', 'pro' ),
        'description'=> esc_html__( 'Desactívalo en producción si no tienes anuncios insertados en los widgets.', 'pro' ),
        'section'  => 'pro_ad_settings',
        'type'     => 'checkbox',
    ) );
}
add_action( 'customize_register', 'pro_customize_register' );

// Include Ad Manager
require_once get_template_directory() . '/inc/ad-manager.php';

/**
 * Metabox para la Firma del Autor
 */
function pro_add_firma_metabox() {
    add_meta_box(
        'pro_firma_autor_metabox',       // ID
        'Firma',                         // Título
        'pro_firma_autor_metabox_html',  // Callback
        'post',                          // Pantalla (Post Type)
        'side',                          // Contexto
        'default'                        // Prioridad
    );
}
add_action( 'add_meta_boxes', 'pro_add_firma_metabox' );

function pro_firma_autor_metabox_html( $post ) {
    $value = get_post_meta( $post->ID, '_pro_firma_autor', true );
    wp_nonce_field( 'pro_firma_autor_nonce_action', 'pro_firma_autor_nonce' );
    ?>
    <label for="pro_firma_autor_field" style="display:block; margin-bottom:5px;">Nombre del autor para la firma:</label>
    <input type="text" id="pro_firma_autor_field" name="pro_firma_autor_field" value="<?php echo esc_attr( $value ); ?>" style="width:100%;" placeholder="Ej: Juan Pérez" />
    <p style="font-size: 12px; color: #666; margin-top: 5px;">Aparecerá en cursiva debajo del título de la noticia.</p>
    <?php
}

function pro_save_firma_autor_meta( $post_id ) {
    if ( ! isset( $_POST['pro_firma_autor_nonce'] ) || ! wp_verify_nonce( $_POST['pro_firma_autor_nonce'], 'pro_firma_autor_nonce_action' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( isset( $_POST['pro_firma_autor_field'] ) ) {
        update_post_meta( $post_id, '_pro_firma_autor', sanitize_text_field( $_POST['pro_firma_autor_field'] ) );
    }
}
add_action( 'save_post', 'pro_save_firma_autor_meta' );

// =============================================================================
// ETIQUETAS EDITORIALES
// Auditoría fix: se eliminó pro_enforce_single_tag() que sobrescribía todas
// las etiquetas de cada artículo con 'EO-2026' al guardar, destruyendo la
// clasificación temática y el valor SEO de la taxonomía.
//
// El año editorial debe almacenarse como metadato interno, no como etiqueta:
//   update_post_meta( $post_id, '_editorial_year', date('Y') );
//
// El panel de etiquetas queda visible en el editor para que los redactores
// puedan clasificar correctamente sus artículos.
// =============================================================================

/**
 * Instalación "Nuclear" de páginas automáticas
 * Esto creará todas las páginas físicas al cargar el dashboard si no existen.
 */
function pro_nuclear_install_pages() {
    // Solo correr una vez para no saturar la base de datos
    if ( get_option( 'pro_pages_installed_nuclear_v7' ) ) {
        return;
    }

    // Asegurarse de que el usuario es admin
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $pages_to_create = array(
        'Contacto'               => 'page-contacto.php',
        'Carteles y Edictos'     => 'page-carteles.php',
        'Términos y Condiciones'  => 'page-terminos-y-condiciones.php',
        'Política de Cookies'    => 'page-politica-de-cookies.php',
        'Política de Privacidad' => 'page-politica-de-privacidad.php',
        'Análisis Internacional' => 'page-categoria.php',
        'Artes y Espectáculos' => 'page-categoria.php',
        'Asamblea Nacional' => 'page-categoria.php',
        'Belleza' => 'page-categoria.php',
        'Bienestar' => 'page-categoria.php',
        'Bitácora Mundial' => 'page-categoria.php',
        'Buen ciudadano' => 'page-categoria.php',
        'Ciencia y Tecnología' => 'page-categoria.php',
        'Cine' => 'page-categoria.php',
        'Ciudad' => 'page-categoria.php',
        'Comunidad' => 'page-categoria.php',
        'Crónica Policial' => 'page-categoria.php',
        'Cultura' => 'page-categoria.php',
        'Deportes' => 'page-categoria.php',
        'Economía' => 'page-categoria.php',
        'Educación' => 'page-categoria.php',
        'Entretenimiento' => 'page-categoria.php',
        'Entrevistas' => 'page-categoria.php',
        'Estilo de vida' => 'page-categoria.php',
        'Farándula' => 'page-categoria.php',
        'Gastronomía' => 'page-categoria.php',
        'Inteligencia Artificial' => 'page-categoria.php',
        'Internacional' => 'page-categoria.php',
        'Literatura' => 'page-categoria.php',
        'Local' => 'page-categoria.php',
        'Mascotas' => 'page-categoria.php',
        'Monagas' => 'page-categoria.php',
        'Mundo' => 'page-categoria.php',
        'Municipios' => 'page-categoria.php',
        'Nacional' => 'page-categoria.php',
        'Nueva Salud' => 'page-categoria.php',
        'Opinión' => 'page-categoria.php',
        'Política' => 'page-categoria.php',
        'Regiones' => 'page-categoria.php',
        'Relevantes' => 'page-categoria.php',
        'Robótica' => 'page-categoria.php',
        'Salud' => 'page-categoria.php',
        'Seguridad' => 'page-categoria.php',
        'Streaming' => 'page-categoria.php',
        'Sucesos' => 'page-categoria.php',
        'Viajes' => 'page-categoria.php',
    );

    foreach ( $pages_to_create as $title => $template ) {
        $page_query = new WP_Query( array(
            'post_type'              => 'page',
            'title'                  => $title,
            'post_status'            => 'all',
            'posts_per_page'         => 1,
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_term_cache' => false,
            'update_post_meta_cache' => false,
            'orderby'                => 'post_date',
            'order'                  => 'DESC',
        ) );
        $page_check = ! empty( $page_query->posts ) ? $page_query->posts[0] : null;

        if ( ! $page_check ) {
            $new_page_id = wp_insert_post( array(
                'post_title'     => $title,
                'post_type'      => 'page',
                'post_status'    => 'publish',
                'post_author'    => 1,
            ) );
            if ( $new_page_id && ! is_wp_error( $new_page_id ) ) {
                if ( $template !== 'page.php' ) {
                    update_post_meta( $new_page_id, '_wp_page_template', $template );
                }
            }
        }
    }

    // Configurar Inicio estático
    $home_query = new WP_Query( array(
        'post_type'              => 'page',
        'title'                  => 'Inicio',
        'post_status'            => 'all',
        'posts_per_page'         => 1,
    ) );
    $home_page = ! empty( $home_query->posts ) ? $home_query->posts[0] : null;
    
    if ( ! $home_page ) {
        $home_page_id = wp_insert_post( array(
            'post_title'     => 'Inicio',
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'post_author'    => 1,
        ) );
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $home_page_id );
    } else {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $home_page->ID );
    }

    // Auto-Crear un menú estructurado y asignarlo
    $menu_name = 'Menú Principal Nuclear v6';
    $menu_exists = wp_get_nav_menu_object( $menu_name );
    $menu_id = 0;

    if ( ! $menu_exists ) {
        $menu_id = wp_create_nav_menu( $menu_name );

        // Obtener IDs de las páginas
        $all_pages = get_posts( array( 'post_type' => 'page', 'numberposts' => -1 ) );
        $page_map = array();
        foreach ( $all_pages as $p ) {
            $page_map[ $p->post_title ] = $p->ID;
        }

        // Definir estructura principal y submenú
        $main_items = array( 'Inicio', 'Nacional', 'Internacional', 'Sucesos', 'Deportes', 'Economía', 'Política', 'Entretenimiento' );
        $all_titles = array_keys( $pages_to_create );
        $submenu_items = array_diff( $all_titles, $main_items );
        // Remove Contacto from submenu to put it at the end of main
        if ( ( $key = array_search( 'Contacto', $submenu_items ) ) !== false ) {
            unset( $submenu_items[ $key ] );
        }

        // Agregar items principales
        foreach ( $main_items as $item_title ) {
            if ( isset( $page_map[ $item_title ] ) ) {
                wp_update_nav_menu_item( $menu_id, 0, array(
                    'menu-item-title'     => $item_title,
                    'menu-item-object-id' => $page_map[ $item_title ],
                    'menu-item-object'    => 'page',
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                ) );
            }
        }

        // Crear item "Más" (Custom Link)
        $mas_item_id = wp_update_nav_menu_item( $menu_id, 0, array(
            'menu-item-title'  => 'Más',
            'menu-item-url'    => '#',
            'menu-item-status' => 'publish',
        ) );

        // Agregar items al submenú "Más"
        // Orden personalizado definido por el usuario
        $custom_order = array(
            'Monagas',
            'Ciudad',
            'Comunidad',
            'Política',
            'Educación',
            'Salud',
            'Municipios',
            'Sucesos',
            'Seguridad',
            'Crónica Policial',
            'Nacional',
            'Regiones',
            'Asamblea Nacional',
            'Economía',
            'Mundo',
            'Análisis Internacional',
            'Bitácora Mundial',
            'Deportes',
            'Artes y Espectáculos',
            'Farándula',
            'Cine',
            'Streaming',
            'Cultura',
            'Literatura',
            'Bienestar',
            'Nueva Salud',
            'Gastronomía',
            'Belleza',
            'Viajes',
            'Estilo de vida',
            'Mascotas',
            'Ciencia y Tecnología',
            'Inteligencia Artificial',
            'Robótica',
            'Opinión',
            'Buen ciudadano'
        );

        $ordered_submenu = array();
        foreach ( $custom_order as $co_item ) {
            if ( in_array( $co_item, $submenu_items ) ) {
                $ordered_submenu[] = $co_item;
            }
        }
        $remaining = array_diff( $submenu_items, $ordered_submenu );
        sort( $remaining );
        $submenu_items = array_merge( $ordered_submenu, $remaining );

        foreach ( $submenu_items as $sub_item ) {
            if ( isset( $page_map[ $sub_item ] ) ) {
                wp_update_nav_menu_item( $menu_id, 0, array(
                    'menu-item-title'     => $sub_item,
                    'menu-item-object-id' => $page_map[ $sub_item ],
                    'menu-item-object'    => 'page',
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                    'menu-item-parent-id' => $mas_item_id,
                ) );
            }
        }

        // Agregar "Contacto" al final
        if ( isset( $page_map['Contacto'] ) ) {
            wp_update_nav_menu_item( $menu_id, 0, array(
                'menu-item-title'     => 'Contacto',
                'menu-item-object-id' => $page_map['Contacto'],
                'menu-item-object'    => 'page',
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ) );
        }
    } else {
        $menu_id = $menu_exists->term_id;
    }

    // Asignar forzosamente el menú a la ubicación 'primary'
    if ( $menu_id ) {
        $locations = get_theme_mod( 'nav_menu_locations' );
        if ( ! is_array( $locations ) ) {
            $locations = array();
        }
        $locations['primary'] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    }

    update_option( 'pro_pages_installed_nuclear_v7', true );
}
add_action( 'admin_init', 'pro_nuclear_install_pages' );

/**
 * Acción manual para re-instalar páginas desde el admin
 * Uso: ir a /wp-admin/?pro_reinstall_pages=1
 */
function pro_handle_manual_reinstall() {
    if ( ! isset( $_GET['pro_reinstall_pages'] ) || $_GET['pro_reinstall_pages'] !== '1' ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    // Verificar nonce
    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'pro_reinstall_pages' ) ) {
        wp_die( 'Acción no autorizada.' );
    }
    // Borrar el flag para permitir re-instalación
    delete_option( 'pro_pages_installed_nuclear_v7' );
    delete_option( 'pro_pages_installed_nuclear_v6' ); // Limpiar flag anterior también
    // Redirigir para que admin_init vuelva a correr
    wp_redirect( admin_url( 'edit.php?post_type=page&pro_reinstall_done=1' ) );
    exit;
}
add_action( 'admin_init', 'pro_handle_manual_reinstall' );



/**
 * Modificaciones del Menú (Flechas y enlaces)
 */
add_filter('nav_menu_item_title', 'pro_add_dropdown_arrow', 10, 4);
function pro_add_dropdown_arrow($title, $item, $args, $depth) {
    if (in_array('menu-item-has-children', $item->classes)) {
        $arrow = ($depth > 0) ? '&#9656;' : '&#9662;'; // Flecha a la derecha para submenús, hacia abajo para principales
        $title .= ' <span class="dropdown-arrow" style="font-size: 0.8em; margin-left: 4px; display: inline-block;">' . $arrow . '</span>'; 
    }
    return $title;
}

add_filter('nav_menu_link_attributes', 'pro_prevent_hash_links', 10, 3);
function pro_prevent_hash_links($atts, $item, $args) {
    if (isset($atts['href']) && $atts['href'] === '#') {
        $atts['href'] = 'javascript:void(0);';
        $atts['style'] = isset($atts['style']) ? $atts['style'] . ' cursor: pointer;' : 'cursor: pointer;';
    }
    return $atts;
}

/**
 * ==============================================================
 * CUSTOM POST TYPE: CARTELES Y EDICTOS
 * ==============================================================
 */

// 1. Registrar el CPT
function pro_register_carteles_cpt() {
    $labels = array(
        'name'                  => 'Carteles',
        'singular_name'         => 'Cartel',
        'menu_name'             => 'Carteles',
        'name_admin_bar'        => 'Cartel',
        'add_new'               => 'Añadir Nuevo',
        'add_new_item'          => 'Añadir Nuevo Cartel',
        'new_item'              => 'Nuevo Cartel',
        'edit_item'             => 'Editar Cartel',
        'view_item'             => 'Ver Cartel',
        'all_items'             => 'Todos los Carteles',
        'search_items'          => 'Buscar Carteles',
        'not_found'             => 'No se encontraron carteles.',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'carteles' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-media-document', // Icono de documento
        'supports'           => array( 'title', 'thumbnail' ), // Solo título e imagen destacada
        'show_in_rest'       => true, // Soporte para Gutenberg si lo desean
    );

    register_post_type( 'cartel', $args );
}
add_action( 'init', 'pro_register_carteles_cpt' );

// 2. Añadir Meta Box para subir PDF
function pro_add_cartel_pdf_metabox() {
    add_meta_box(
        'pro_cartel_pdf',
        'Documento PDF del Cartel',
        'pro_render_cartel_pdf_metabox',
        'cartel',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'pro_add_cartel_pdf_metabox' );

function pro_enqueue_media_for_cartel() {
    global $typenow;
    if ( $typenow == 'cartel' ) {
        wp_enqueue_media();
    }
}
add_action( 'admin_enqueue_scripts', 'pro_enqueue_media_for_cartel' );

function pro_render_cartel_pdf_metabox( $post ) {
    // Añadir wp_nonce para seguridad
    wp_nonce_field( 'pro_save_cartel_pdf_data', 'pro_cartel_pdf_meta_box_nonce' );

    // Obtener valor actual
    $value = get_post_meta( $post->ID, '_cartel_pdf_url', true );

    echo '<label for="pro_cartel_pdf_url" style="display:block; margin-bottom:5px;">URL del archivo PDF: </label>';
    echo '<div style="display: flex; gap: 10px;">';
    echo '<input type="url" id="pro_cartel_pdf_url" name="pro_cartel_pdf_url" value="' . esc_attr( $value ) . '" style="flex-grow: 1;" placeholder="https://..." />';
    echo '<button type="button" class="button button-secondary" id="pro_upload_pdf_button">Seleccionar PDF en Medios</button>';
    echo '</div>';
    echo '<p class="description">Puedes pegar el enlace directo, o usar el botón para elegir/subir un PDF desde la biblioteca.</p>';
    
    // Script JS para manejar el botón
    ?>
    <script>
    jQuery(document).ready(function($){
        var mediaUploader;
        $('#pro_upload_pdf_button').click(function(e) {
            e.preventDefault();
            
            // Si el frame ya existe, ábrelo
            if (mediaUploader) {
                mediaUploader.open();
                return;
            }
            
            // Extiende el frame de medios
            mediaUploader = wp.media({
                title: 'Seleccionar Archivo PDF',
                button: {
                    text: 'Usar este archivo'
                },
                library: {
                    type: 'application/pdf'
                },
                multiple: false
            });
            
            // Cuando se selecciona un archivo
            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#pro_cartel_pdf_url').val(attachment.url);
            });
            
            // Abre el frame
            mediaUploader.open();
        });
    });
    </script>
    <?php
}

// 3. Guardar el valor del Meta Box
function pro_save_cartel_pdf_data( $post_id ) {
    if ( ! isset( $_POST['pro_cartel_pdf_meta_box_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['pro_cartel_pdf_meta_box_nonce'], 'pro_save_cartel_pdf_data' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( ! isset( $_POST['pro_cartel_pdf_url'] ) ) {
        return;
    }

    $my_data = esc_url_raw( wp_unslash( $_POST['pro_cartel_pdf_url'] ) );
    update_post_meta( $post_id, '_cartel_pdf_url', $my_data );
}
add_action( 'save_post', 'pro_save_cartel_pdf_data' );

/**
 * ==============================================================
 * SISTEMA DE FORMULARIO DE CONTACTO (CPT, AJAX, EXPORTACIÓN)
 * ==============================================================
 */

// 1. Registrar CPT "Mensajes"
function pro_register_mensajes_cpt() {
    $labels = array(
        'name'               => 'Mensajes',
        'singular_name'      => 'Mensaje',
        'menu_name'          => 'Mensajes',
        'name_admin_bar'     => 'Mensaje',
        'all_items'          => 'Todos los Mensajes',
        'search_items'       => 'Buscar Mensajes',
        'not_found'          => 'No se encontraron mensajes.',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false, // No público en front-end nativo
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 6,
        'menu_icon'          => 'dashicons-email',
        'capability_type'    => 'post',
        'capabilities'       => array(
            'create_posts' => 'do_not_allow', // Solo entra por AJAX (o admin manual, pero ocultamos botón default)
        ),
        'map_meta_cap'       => true,
        'supports'           => array( 'title', 'editor', 'custom-fields' ), // Titulo = Nombre, Editor = Mensaje
    );

    register_post_type( 'mensaje_contacto', $args );
}
add_action( 'init', 'pro_register_mensajes_cpt' );

// 2. Personalizar columnas del listado en el Admin
add_filter( 'manage_mensaje_contacto_posts_columns', 'pro_set_custom_mensaje_columns' );
function pro_set_custom_mensaje_columns( $columns ) {
    unset( $columns['date'] );
    $columns['title'] = 'Remitente (Nombre)';
    $columns['email'] = 'Correo';
    $columns['phone'] = 'Teléfono';
    $columns['depto'] = 'Departamento';
    $columns['date']  = 'Fecha';
    return $columns;
}

add_action( 'manage_mensaje_contacto_posts_custom_column' , 'pro_custom_mensaje_column', 10, 2 );
function pro_custom_mensaje_column( $column, $post_id ) {
    switch ( $column ) {
        case 'email' :
            echo esc_html( get_post_meta( $post_id, '_contacto_email', true ) ); 
            break;
        case 'phone' :
            echo esc_html( get_post_meta( $post_id, '_contacto_phone', true ) ); 
            break;
        case 'depto' :
            echo esc_html( get_post_meta( $post_id, '_contacto_depto', true ) ); 
            break;
    }
}

// 3. Endpoint AJAX para guardar el formulario
add_action( 'wp_ajax_nopriv_pro_submit_contact_form', 'pro_submit_contact_form' );
add_action( 'wp_ajax_pro_submit_contact_form', 'pro_submit_contact_form' );

function pro_submit_contact_form() {
    check_ajax_referer( 'pro_ajax_nonce', 'nonce' );

    $required_fields = [ 'name', 'email', 'phone', 'address', 'department', 'message' ];
    foreach ( $required_fields as $field ) {
        if ( empty( $_POST[ $field ] ) ) {
            // wp_send_json_error llama wp_die() internamente, no repetir
            wp_send_json_error( array( 'message' => 'Por favor, completa todos los campos obligatorios.' ) );
        }
    }

    $name       = sanitize_text_field( $_POST['name'] );
    $email      = sanitize_email( $_POST['email'] );
    $phone      = sanitize_text_field( $_POST['phone'] );
    $address    = sanitize_text_field( $_POST['address'] );
    $department = sanitize_text_field( $_POST['department'] );
    $message    = sanitize_textarea_field( $_POST['message'] );

    if ( ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => 'El correo electrónico no es válido.' ) );
    }

    // Seguridad Extrema: Evitar Inyecciones, Enlaces y Código
    $security_check = $name . ' ' . $address . ' ' . $message;
    
    // Bloquear enlaces
    if ( preg_match( '/(http|https|ftp|ftps):\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/', $security_check ) || stripos( $security_check, 'www.' ) !== false || stripos( $security_check, '.com/' ) !== false ) {
        wp_send_json_error( array( 'message' => 'Por seguridad, no se permiten enlaces ni URLs en el formulario.' ) );
    }

    // Bloquear HTML, scripts e inyecciones SQL básicas
    if ( preg_match( '/(<|>|\[url|\[link|script|union select|drop table|concat\(|-- )/i', $security_check ) ) {
        wp_send_json_error( array( 'message' => 'Se han detectado caracteres especiales o comandos no permitidos en el texto.' ) );
    }

    $post_data = array(
        'post_title'   => $name,
        'post_content' => $message,
        'post_status'  => 'publish',
        'post_type'    => 'mensaje_contacto',
    );

    $post_id = wp_insert_post( $post_data );

    if ( $post_id ) {
        update_post_meta( $post_id, '_contacto_email', $email );
        update_post_meta( $post_id, '_contacto_phone', $phone );
        update_post_meta( $post_id, '_contacto_address', $address );
        update_post_meta( $post_id, '_contacto_depto', $department );

        wp_send_json_success( array( 'message' => '¡Mensaje enviado con éxito!' ) );
    } else {
        wp_send_json_error( array( 'message' => 'Hubo un error al guardar tu mensaje.' ) );
    }
    // wp_send_json_success/error ya llama wp_die() — no es necesario aquí
}

// 4. Botón de Exportar a CSV en la vista del CPT
add_action('manage_posts_extra_tablenav', 'pro_export_mensajes_button', 20, 1);
function pro_export_mensajes_button($which) {
    global $typenow;
    if ('mensaje_contacto' === $typenow && 'top' === $which) {
        $export_url = add_query_arg(array(
            'action' => 'pro_export_mensajes_csv',
            '_wpnonce' => wp_create_nonce('pro_export_mensajes_csv_nonce')
        ), admin_url('admin-post.php'));
        
        echo '<div class="alignleft actions">';
        echo '<a href="' . esc_url($export_url) . '" class="button button-primary">Descargar Excel/CSV</a>';
        echo '</div>';
    }
}

// 5. Lógica de exportación CSV
add_action('admin_post_pro_export_mensajes_csv', 'pro_export_mensajes_csv_handler');
function pro_export_mensajes_csv_handler() {
    if ( ! current_user_can( 'export_contact_messages' ) ) {
        wp_die( esc_html__( 'No tienes permiso para exportar contactos.', 'pro' ), '', array( 'response' => 403 ) );
    }
    check_admin_referer('pro_export_mensajes_csv_nonce');

    $args = array(
        'post_type'      => 'mensaje_contacto',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );
    $query = new WP_Query($args);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=mensajes_contacto_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    // Bom UTF-8 para Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeceras
    fputcsv($output, array('Fecha', 'Nombre', 'Correo', 'Teléfono', 'Dirección', 'Departamento', 'Mensaje'));

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            fputcsv($output, array(
                get_the_date('Y-m-d H:i:s'),
                get_the_title(),
                get_post_meta($post_id, '_contacto_email', true),
                get_post_meta($post_id, '_contacto_phone', true),
                get_post_meta($post_id, '_contacto_address', true),
                get_post_meta($post_id, '_contacto_depto', true),
                get_the_content()
            ));
        }
    }
    fclose($output);
    exit();
}

/**
 * Cargar personalizaciones del panel de administración (White-label)
 */
require_once get_template_directory() . '/inc/admin-whitelabel.php';

/**
 * Cargar configuraciones de seguridad (Login URL y prevención)
 */
require_once get_template_directory() . '/inc/security.php';

/**
 * Cargar gestor de Portada del Día (Programación de portadas a las 05:00 AM)
 */
require_once get_template_directory() . '/inc/portada-dia.php';

/**
 * Registrar temas de color personalizados para el panel de administración
 * y forzar su carga según el rol del usuario ('direccion' o 'autor').
 */
function pro_register_and_force_admin_colors() {
    // 1. Registrar esquema para Dirección (Guinda / Antracita)
    wp_admin_css_color(
        'pro_direccion_scheme',
        'Dirección (Editorial)',
        get_template_directory_uri() . '/assets/css/admin-direccion.css',
        array( '#111827', '#1f2937', '#7f1d1d', '#ef4444' ),
        array( 'base' => '#7f1d1d', 'focus' => '#ef4444', 'current' => '#ef4444' )
    );

    // 2. Registrar esquema para Autor (Bosque / Esmeralda)
    wp_admin_css_color(
        'pro_autor_scheme',
        'Autor (Redacción)',
        get_template_directory_uri() . '/assets/css/admin-autor.css',
        array( '#0f172a', '#1e293b', '#065f46', '#10b981' ),
        array( 'base' => '#065f46', 'focus' => '#10b981', 'current' => '#10b981' )
    );
}
add_action( 'admin_init', 'pro_register_and_force_admin_colors' );

/**
 * Forzar el esquema de color de administración según el rol de usuario
 */
function pro_force_admin_color_by_role( $result, $option, $user ) {
    if ( 'admin_color' === $option && is_a( $user, 'WP_User' ) ) {
        if ( in_array( 'direccion', $user->roles ) || in_array( 'publicista', $user->roles ) ) {
            return 'pro_direccion_scheme';
        } elseif ( in_array( 'autor', $user->roles ) || in_array( 'author', $user->roles ) ) {
            return 'pro_autor_scheme';
        }
    }
    return $result;
}
add_filter( 'get_user_option_admin_color', 'pro_force_admin_color_by_role', 10, 3 );

/**
 * ==============================================================
 * REGLA NUCLEAR Y SISTEMA DE SUSPENSIÓN TEMPORAL DE USUARIOS
 * ==============================================================
 */

/**
 * 1. REGLA NUCLEAR: Impedir que usuarios con el rol 'direccion' puedan borrar a cualquier usuario.
 * Solo los administradores conservan este privilegio absoluto. Mapeado al core profundo de capacidades.
 */
function pro_restrict_user_deletion( $caps, $cap, $user_id, $args ) {
    if ( in_array( $cap, array( 'delete_user', 'delete_users' ), true ) ) {
        $current_user = get_userdata( $user_id );
        if ( $current_user && in_array( 'direccion', $current_user->roles, true ) ) {
            $caps = array( 'do_not_allow' );
        }
    }
    return $caps;
}
add_filter( 'map_meta_cap', 'pro_restrict_user_deletion', 10, 4 );

/**
 * 2. Registrar la columna "Estado" en la tabla de usuarios de WordPress.
 */
function pro_add_user_status_column( $columns ) {
    $columns['pro_status'] = 'Estado';
    return $columns;
}
add_filter( 'manage_users_columns', 'pro_add_user_status_column' );

/**
 * Renderizar el valor de la columna "Estado" con badges premium.
 */
function pro_render_user_status_column_value( $val, $column_name, $user_id ) {
    if ( 'pro_status' === $column_name ) {
        $status = get_user_meta( $user_id, '_pro_user_status', true );
        if ( 'suspended' === $status ) {
            return '<span class="pro-status-badge suspended">Suspendido</span>';
        } else {
            return '<span class="pro-status-badge active">Activo</span>';
        }
    }
    return $val;
}
add_filter( 'manage_users_custom_column', 'pro_render_user_status_column_value', 10, 3 );

/**
 * 3. Agregar los enlaces rápidos de "Suspender" y "Reactivar" bajo el nombre del usuario.
 */
function pro_user_suspension_row_actions( $actions, $user_object ) {
    $current_user = wp_get_current_user();
    
    // Si el usuario actual no es admin ni direccion, no mostrar nada.
    $is_admin = in_array( 'administrator', $current_user->roles );
    $is_director = in_array( 'direccion', $current_user->roles ) || in_array( 'gerencia', $current_user->roles );
    if ( ! $is_admin && ! $is_director ) {
        return $actions;
    }

    // No permitir que un usuario se suspenda a sí mismo.
    if ( $current_user->ID === $user_object->ID ) {
        return $actions;
    }

    // Un director NO puede suspender a un administrador.
    $target_is_admin = in_array( 'administrator', $user_object->roles );
    if ( $is_director && $target_is_admin ) {
        return $actions;
    }

    // Si el usuario es director o gerencia, solo puede suspender/reactivar a perfiles inferiores
    if ( $is_director ) {
        $allowed_roles = array( 'gerencia', 'direccion', 'autor', 'author' );
        $has_allowed_role = false;
        foreach ( $user_object->roles as $role ) {
            if ( in_array( $role, $allowed_roles ) ) {
                $has_allowed_role = true;
                break;
            }
        }
        if ( ! $has_allowed_role ) {
            return $actions;
        }
    }

    // Determinar estado actual del usuario objetivo
    $status = get_user_meta( $user_object->ID, '_pro_user_status', true );

    if ( 'suspended' === $status ) {
        $nonce = wp_create_nonce( 'pro_reactivate_user_' . $user_object->ID );
        $url = add_query_arg(
            array(
                'action'   => 'pro_reactivate_user',
                'user'     => $user_object->ID,
                '_wpnonce' => $nonce,
            ),
            admin_url( 'users.php' )
        );
        $actions['pro_reactivate'] = sprintf(
            '<a href="%s" class="pro-action-reactivate" style="color: #10b981; font-weight: 600;">Reactivar</a>',
            esc_url( $url )
        );
    } else {
        $nonce = wp_create_nonce( 'pro_suspend_user_' . $user_object->ID );
        $url = add_query_arg(
            array(
                'action'   => 'pro_suspend_user',
                'user'     => $user_object->ID,
                '_wpnonce' => $nonce,
            ),
            admin_url( 'users.php' )
        );
        $actions['pro_suspend'] = sprintf(
            '<a href="%s" class="pro-action-suspend" style="color: #ef4444; font-weight: 600;" onclick="return confirm(\'¿Estás seguro de que deseas SUSPENDER temporalmente este perfil de usuario? Se cerrarán todas sus sesiones activas de inmediato.\');">Suspender</a>',
            esc_url( $url )
        );
    }

    return $actions;
}
add_filter( 'user_row_actions', 'pro_user_suspension_row_actions', 10, 2 );

/**
 * 4. Procesar las acciones de suspensión y reactivación en el panel de administración.
 */
function pro_handle_user_status_actions() {
    if ( ! is_admin() ) {
        return;
    }

    $action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
    if ( ! in_array( $action, array( 'pro_suspend_user', 'pro_reactivate_user' ), true ) ) {
        return;
    }

    $target_user_id = isset( $_GET['user'] ) ? absint( $_GET['user'] ) : 0;
    if ( ! $target_user_id ) {
        return;
    }

    $nonce_action = $action . '_' . $target_user_id;
    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], $nonce_action ) ) {
        wp_die( 'Error de seguridad: Solicitud no verificada (Nonce inválido).' );
    }

    $current_user = wp_get_current_user();
    $is_admin = in_array( 'administrator', $current_user->roles );
    $is_director = in_array( 'direccion', $current_user->roles ) || in_array( 'gerencia', $current_user->roles );

    // Validar permisos del ejecutor
    if ( ! $is_admin && ! $is_director ) {
        wp_die( 'No tienes privilegios suficientes para realizar esta acción.' );
    }

    // No permitir auto-suspensión
    if ( $current_user->ID === $target_user_id ) {
        wp_die( 'No puedes suspender o reactivar tu propia cuenta.' );
    }

    $target_user = get_userdata( $target_user_id );
    if ( ! $target_user ) {
        wp_die( 'El usuario de destino no existe.' );
    }

    // Un director NO puede suspender a un administrador.
    $target_is_admin = in_array( 'administrator', $target_user->roles );
    if ( $is_director && $target_is_admin ) {
        wp_die( 'Acceso denegado: Un Director no puede gestionar cuentas de Administradores.' );
    }

    // Si el usuario es director, verificar restricciones de rol objetivo
    if ( $is_director ) {
        $allowed_roles = array( 'direccion', 'autor', 'author' );
        $has_allowed_role = false;
        foreach ( $target_user->roles as $role ) {
            if ( in_array( $role, $allowed_roles ) ) {
                $has_allowed_role = true;
                break;
            }
        }
        if ( ! $has_allowed_role ) {
            wp_die( 'Acceso denegado: No tienes privilegios para modificar el estado de este tipo de usuario.' );
        }
    }

    // Procesar acción
    if ( 'pro_suspend_user' === $action ) {
        update_user_meta( $target_user_id, '_pro_user_status', 'suspended' );
        
        // Destruir todas las sesiones del usuario de forma inmediata (deslogueo forzado nuclear)
        wp_destroy_user_sessions( $target_user_id );

        $redirect_url = add_query_arg( array( 'pro_msg' => 'suspended' ), admin_url( 'users.php' ) );
        wp_safe_redirect( $redirect_url );
        exit;
    } elseif ( 'pro_reactivate_user' === $action ) {
        update_user_meta( $target_user_id, '_pro_user_status', 'active' );

        $redirect_url = add_query_arg( array( 'pro_msg' => 'reactivated' ), admin_url( 'users.php' ) );
        wp_safe_redirect( $redirect_url );
        exit;
    }
}
add_action( 'admin_init', 'pro_handle_user_status_actions' );

/**
 * 5. Mostrar notificaciones administrativas sobre la suspensión/reactivación.
 */
function pro_user_status_admin_notices() {
    global $pagenow;
    if ( 'users.php' !== $pagenow ) {
        return;
    }

    $msg = isset( $_GET['pro_msg'] ) ? sanitize_text_field( $_GET['pro_msg'] ) : '';
    if ( 'suspended' === $msg ) {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Éxito:</strong> El perfil de usuario ha sido suspendido temporalmente y todas sus sesiones activas han sido cerradas de inmediato.</p></div>';
    } elseif ( 'reactivated' === $msg ) {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Éxito:</strong> El perfil de usuario ha sido reactivado exitosamente y ya puede iniciar sesión en la plataforma.</p></div>';
    }
}
add_action( 'admin_notices', 'pro_user_status_admin_notices' );

/**
 * 6. Interceptar el intento de inicio de sesión de los usuarios suspendidos.
 */
function pro_block_suspended_user_login( $user, $username ) {
    // Si ya hay un error previo en la autenticación, seguir de largo
    if ( is_wp_error( $user ) ) {
        return $user;
    }

    if ( is_a( $user, 'WP_User' ) ) {
        $status = get_user_meta( $user->ID, '_pro_user_status', true );
        if ( 'suspended' === $status ) {
            $error_msg = '<div class="pro-login-blocked-notice" style="text-align: left; line-height: 1.6;">';
            $error_msg .= '<strong style="color: #ef4444; font-size: 15px; display: block; margin-bottom: 8px;">⚠️ Acceso Restringido</strong>';
            $error_msg .= 'Tu cuenta de usuario ha sido <strong>suspendida temporalmente</strong> por decisión de la Dirección Editorial o la Administración.';
            $error_msg .= '<br/><br/>Si consideras que esto es un error o necesitas solicitar la reactivación de tu perfil, ponte en contacto con los Administradores de la plataforma.';
            $error_msg .= '</div>';

            return new WP_Error( 'pro_user_suspended', $error_msg );
        }
    }

    return $user;
}
add_filter( 'wp_authenticate_user', 'pro_block_suspended_user_login', 99, 2 );

/**
 * 7. Estilos premium y scripts visuales complementarios en el panel de administración.
 */
function pro_user_status_admin_styles() {
    global $pagenow;
    if ( 'users.php' !== $pagenow ) {
        return;
    }
    ?>
    <style>
        .column-pro_status {
            width: 130px;
        }
        .pro-status-badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            line-height: 1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 12px;
            text-align: center;
        }
        .pro-status-badge.active {
            background-color: rgba(16, 185, 129, 0.12) !important;
            color: #10b981 !important;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        .pro-status-badge.suspended {
            background-color: rgba(239, 68, 68, 0.12) !important;
            color: #ef4444 !important;
            border: 1px solid rgba(239, 68, 68, 0.25);
        }
        /* Resaltar fila suspendida en el listado */
        tr.user-suspended-row {
            background-color: #fff5f5 !important;
        }
        tr.user-suspended-row:hover {
            background-color: #ffebeb !important;
        }
    </style>
    <?php
}
add_action( 'admin_head', 'pro_user_status_admin_styles' );

function pro_user_status_admin_footer_scripts() {
    global $pagenow;
    if ( 'users.php' !== $pagenow ) {
        return;
    }
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.pro-status-badge.suspended').closest('tr').addClass('user-suspended-row').css({
                'border-left': '4px solid #ef4444'
            });
        });
    </script>
    <?php
}
add_action( 'admin_footer', 'pro_user_status_admin_footer_scripts' );

/**
 * ==============================================================
 * SISTEMA DE WIDGETS DE ESCRITORIO (DASHBOARD) Y TRACKING DE AUTORES
 * ==============================================================
 */

/**
 * 1. RASTREADOR DE CONEXIÓN Y SESIONES
 * Guarda la última sesión absoluta y el primer ingreso diario de cada usuario.
 */
function pro_track_user_login_activity( $user_login, $user ) {
    $now = current_time( 'mysql' ); // Timestamp en la hora local del sitio
    $today = current_time( 'Y-m-d' );
    
    // Guardar fecha/hora de la última sesión
    update_user_meta( $user->ID, '_pro_last_login_time', $now );
    
    // Guardar primer inicio de sesión de hoy (si no existe registro previo hoy)
    $first_login = get_user_meta( $user->ID, '_pro_first_login_today', true );
    if ( empty( $first_login ) || substr( $first_login, 0, 10 ) !== $today ) {
        update_user_meta( $user->ID, '_pro_first_login_today', $now );
    }
}
add_action( 'wp_login', 'pro_track_user_login_activity', 10, 2 );

/**
 * Función auxiliar para dar formato legible de tiempo transcurrido (Español Premium)
 */
function pro_get_human_time_diff( $mysql_date_string ) {
    if ( empty( $mysql_date_string ) ) {
        return 'Sin registro';
    }
    $timestamp = strtotime( $mysql_date_string );
    if ( ! $timestamp ) {
        return 'Sin registro';
    }
    
    $diff = current_time( 'timestamp' ) - $timestamp;
    if ( $diff < 0 ) {
        $diff = 0;
    }
    
    if ( $diff < MINUTE_IN_SECONDS ) {
        return 'Hace unos instantes';
    } elseif ( $diff < HOUR_IN_SECONDS ) {
        $mins = round( $diff / MINUTE_IN_SECONDS );
        return sprintf( 'Hace %s %s', $mins, _n( 'minuto', 'minutos', $mins, 'pro' ) );
    } elseif ( $diff < DAY_IN_SECONDS ) {
        $hours = round( $diff / HOUR_IN_SECONDS );
        return sprintf( 'Hace %s %s', $hours, _n( 'hora', 'horas', $hours, 'pro' ) );
    } else {
        return date_i18n( 'j \d\e F, g:i a', $timestamp );
    }
}

/**
 * 2. REGISTRAR LOS WIDGETS EN EL DASHBOARD DE WORDPRESS
 */
function pro_register_dashboard_widgets() {
    $current_user = wp_get_current_user();
    $is_admin = in_array( 'administrator', $current_user->roles );
    $is_director = in_array( 'direccion', $current_user->roles ) || in_array( 'gerencia', $current_user->roles ) || in_array( 'publicista', $current_user->roles );
    
    // 1. Portada del Día - Todos los roles lo ven
    wp_add_dashboard_widget( 
        'pro_dashboard_portada', 
        '📰 Portada del Día', 
        'pro_render_dashboard_portada_widget' 
    );
    
    // 2. Clientes y Fechas de Corte - Solo Admin y Dirección
    if ( $is_admin || $is_director ) {
        wp_add_dashboard_widget( 
            'pro_dashboard_clientes', 
            '👥 Gestión de Clientes Activos', 
            'pro_render_dashboard_clientes_widget' 
        );
    }
    
    // 3. Leads (CPT Mensajes) - Solo Admin y Dirección
    if ( $is_admin || $is_director ) {
        wp_add_dashboard_widget( 
            'pro_dashboard_leads', 
            '📩 Leads de Contacto Recientes', 
            'pro_render_dashboard_leads_widget' 
        );
    }
    
    // 4. Últimas Publicaciones - Todos lo ven (filtrado por rol en el renderizado)
    wp_add_dashboard_widget( 
        'pro_dashboard_publicaciones', 
        '✏️ Últimas Publicaciones', 
        'pro_render_dashboard_publicaciones_widget' 
    );
    
    // 5. Actividad y Sesiones de Autores - Todos lo ven (filtrado por rol en el renderizado)
    wp_add_dashboard_widget( 
        'pro_dashboard_actividad_autores', 
        '⏱️ Registro Diario de Actividad', 
        'pro_render_dashboard_actividad_autores_widget' 
    );
}
add_action( 'wp_dashboard_setup', 'pro_register_dashboard_widgets' );

/**
 * 3. RENDERIZADO DE LOS WIDGETS
 */

// WIDGET 1: Portada del Día
function pro_render_dashboard_portada_widget() {
    $portada_actual = get_option( 'pro_portada_actual', '' );
    $portada_reemplazo = get_option( 'pro_portada_reemplazo', '' );
    $portada_reemplazo_fecha = get_option( 'pro_portada_reemplazo_fecha', '' );
    
    echo '<div class="pro-widget-container portada">';
    if ( ! empty( $portada_actual ) ) {
        echo '<div class="pro-portada-preview-container">';
        echo '<img src="' . esc_url( $portada_actual ) . '" class="pro-portada-preview-img" />';
        echo '<div class="pro-portada-status-tag">PUBLICADA ACTUALMENTE</div>';
        echo '</div>';
    } else {
        echo '<div class="pro-empty-state"><span class="dashicons dashicons-format-image"></span><p>No se ha seleccionado ninguna portada activa.</p></div>';
    }
    
    if ( ! empty( $portada_reemplazo ) && ! empty( $portada_reemplazo_fecha ) ) {
        $scheduled_time = date_i18n( 'j \d\e F \a \l\a\s 05:00 AM', strtotime( $portada_reemplazo_fecha ) );
        echo '<div class="pro-portada-scheduled-banner">';
        echo '<span class="dashicons dashicons-calendar-alt"></span>';
        echo '<div>';
        echo '<strong>Edición Programada:</strong>';
        echo '<p>Reemplazo automático para el ' . esc_html( $scheduled_time ) . '</p>';
        echo '</div>';
        echo '</div>';
    }
    
    $current_user = wp_get_current_user();
    if ( in_array( 'administrator', $current_user->roles ) || in_array( 'direccion', $current_user->roles ) || in_array( 'gerencia', $current_user->roles ) || in_array( 'publicista', $current_user->roles ) ) {
        echo '<div class="pro-widget-actions">';
        echo '<a href="' . esc_url( admin_url( 'admin.php?page=portada-dia' ) ) . '" class="button button-primary"><span class="dashicons dashicons-edit"></span> Gestionar Portadas</a>';
        echo '</div>';
    }
    echo '</div>';
}

// WIDGET 2: Clientes Activos y Fechas de Corte
function pro_render_dashboard_clientes_widget() {
    $clients = get_option( 'pro_active_clients', array() );
    
    // Sembrar datos de demostración premium para la primera carga si está vacío
    if ( empty( $clients ) ) {
        $clients = array(
            array(
                'id' => 1,
                'name' => 'El Heraldo Editorial',
                'plan' => 'SaaS Premium',
                'cost' => '$150/mes',
                'status' => 'paid',
                'cut_off' => date( 'Y-m-d', strtotime( '+12 days' ) )
            ),
            array(
                'id' => 2,
                'name' => 'El Imparcial de Redacción',
                'plan' => 'Básico',
                'cost' => '$80/mes',
                'status' => 'pending',
                'cut_off' => date( 'Y-m-d', strtotime( '+4 days' ) )
            )
        );
        update_option( 'pro_active_clients', $clients );
    }
    
    $current_user = wp_get_current_user();
    $is_admin = in_array( 'administrator', $current_user->roles );
    
    echo '<div class="pro-widget-container clients">';
    echo '<table class="wp-list-table widefat fixed striped pro-clients-table">';
    echo '<thead><tr><th>Cliente</th><th>Plan</th><th>Corte</th><th>Estado</th>';
    if ( $is_admin ) {
        echo '<th style="width: 50px; text-align: center;">Eliminar</th>';
    }
    echo '</tr></thead>';
    echo '<tbody id="pro-clients-list-body">';
    
    foreach ( $clients as $client ) {
        $status_class = ( $client['status'] === 'paid' ) ? 'paid' : 'pending';
        $status_label = ( $client['status'] === 'paid' ) ? 'PAGADO' : 'PENDIENTE';
        $cut_off_timestamp = strtotime( $client['cut_off'] );
        $cut_off_formatted = date_i18n( 'j \d\e F, Y', $cut_off_timestamp );
        
        // Alerta de corte inminente
        $days_left = ceil( ( $cut_off_timestamp - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );
        $alert_class = '';
        if ( $client['status'] !== 'paid' && $days_left <= 5 ) {
            $alert_class = ' pro-alert-danger';
        }
        
        echo '<tr id="pro-client-row-' . esc_attr( $client['id'] ) . '" class="' . esc_attr( $alert_class ) . '">';
        echo '<td><strong>' . esc_html( $client['name'] ) . '</strong><br/><span style="color:#94a3b8; font-size:10px;">' . esc_html( $client['cost'] ) . '</span></td>';
        echo '<td>' . esc_html( $client['plan'] ) . '</td>';
        echo '<td>' . esc_html( $cut_off_formatted ) . '</td>';
        echo '<td><span class="pro-client-status-badge ' . esc_attr( $status_class ) . '">' . esc_html( $status_label ) . '</span></td>';
        if ( $is_admin ) {
            echo '<td style="text-align: center;"><button type="button" class="pro-btn-delete-client" data-id="' . esc_attr( $client['id'] ) . '" title="Eliminar Cliente"><span class="dashicons dashicons-trash"></span></button></td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table>';
    
    if ( $is_admin ) {
        echo '<div class="pro-add-client-toggle-container">';
        echo '<button type="button" class="button" id="pro-toggle-add-client-form"><span class="dashicons dashicons-plus"></span> Registrar Cliente SaaS</button>';
        echo '</div>';
        
        echo '<div id="pro-add-client-form-container" style="display:none; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:15px; margin-top:15px;">';
        echo '<h4 style="margin-top:0; margin-bottom:12px; font-weight:700; color:#0f172a; font-size:12px; border-bottom:1px solid #e2e8f0; padding-bottom:6px;">Registrar Nuevo Cliente SaaS</h4>';
        echo '<div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">';
        echo '<div><label style="display:block; font-size:11px; font-weight:600; color:#475569; margin-bottom:4px;">Nombre del Cliente</label><input type="text" id="pro-new-client-name" style="width:100%;" placeholder="Ej. El Debate Editorial"></div>';
        echo '<div><label style="display:block; font-size:11px; font-weight:600; color:#475569; margin-bottom:4px;">Plan Contratado</label>';
        echo '<select id="pro-new-client-plan" style="width:100%;">';
        echo '<option value="SaaS Premium">SaaS Premium</option>';
        echo '<option value="Básico">Básico</option>';
        echo '<option value="Enterprise">Enterprise</option>';
        echo '</select></div>';
        echo '</div>';
        echo '<div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">';
        echo '<div><label style="display:block; font-size:11px; font-weight:600; color:#475569; margin-bottom:4px;">Costo Mensual</label><input type="text" id="pro-new-client-cost" style="width:100%;" value="$150/mes"></div>';
        echo '<div><label style="display:block; font-size:11px; font-weight:600; color:#475569; margin-bottom:4px;">Próxima Fecha de Corte</label><input type="date" id="pro-new-client-cutoff" style="width:100%;" value="' . date( 'Y-m-d', strtotime( '+30 days' ) ) . '"></div>';
        echo '</div>';
        echo '<div style="display:flex; justify-content:space-between; align-items:center;">';
        echo '<div><label style="font-size:11px; font-weight:600; color:#475569;"><input type="checkbox" id="pro-new-client-paid" checked> ¿Cliente Solventado?</label></div>';
        echo '<div><button type="button" class="button" id="pro-cancel-client-btn" style="margin-right:5px;">Cancelar</button><button type="button" class="button button-primary" id="pro-submit-client-btn">Guardar</button></div>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
}

// WIDGET 3: Leads de Contacto
function pro_render_dashboard_leads_widget() {
    $args = array(
        'post_type'      => 'mensaje_contacto',
        'posts_per_page' => 5,
        'post_status'    => 'publish',
    );
    $query = new WP_Query( $args );
    
    echo '<div class="pro-widget-container leads">';
    if ( $query->have_posts() ) {
        echo '<ul class="pro-leads-feed">';
        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();
            $email = get_post_meta( $post_id, '_contacto_email', true );
            $depto = get_post_meta( $post_id, '_contacto_depto', true );
            $date = get_the_date( 'g:i a \d\e\l j/n/y' );
            
            echo '<li class="pro-lead-item">';
            echo '<div class="pro-lead-header">';
            echo '<strong>' . esc_html( get_the_title() ) . '</strong>';
            echo '<span class="pro-lead-date">' . esc_html( $date ) . '</span>';
            echo '</div>';
            echo '<div class="pro-lead-meta">';
            echo '<span class="pro-lead-meta-item"><span class="dashicons dashicons-email"></span> ' . esc_html( $email ) . '</span>';
            echo '<span class="pro-lead-meta-item"><span class="dashicons dashicons-category"></span> ' . esc_html( $depto ) . '</span>';
            echo '</div>';
            echo '<p class="pro-lead-excerpt">' . esc_html( wp_trim_words( get_the_content(), 12, '...' ) ) . '</p>';
            echo '</li>';
        }
        echo '</ul>';
        wp_reset_postdata();
        
        echo '<div class="pro-widget-actions" style="margin-top:15px;">';
        echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=mensaje_contacto' ) ) . '" class="button"><span class="dashicons dashicons-email-alt"></span> Ver Todos los Leads</a>';
        echo '</div>';
    } else {
        echo '<div class="pro-empty-state"><span class="dashicons dashicons-email"></span><p>No se han recibido mensajes de contacto recientemente.</p></div>';
    }
    echo '</div>';
}

// WIDGET 4: Últimas Publicaciones
function pro_render_dashboard_publicaciones_widget() {
    $current_user = wp_get_current_user();
    $is_admin = in_array( 'administrator', $current_user->roles );
    $is_director = in_array( 'direccion', $current_user->roles ) || in_array( 'gerencia', $current_user->roles ) || in_array( 'publicista', $current_user->roles );
    
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 5,
        'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
    );
    
    // Si no es admin/director, restringir a sus publicaciones propias
    if ( ! $is_admin && ! $is_director ) {
        $args['author'] = $current_user->ID;
    }
    
    $query = new WP_Query( $args );
    
    echo '<div class="pro-widget-container posts">';
    if ( $query->have_posts() ) {
        echo '<ul class="pro-posts-feed">';
        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();
            $author_id = get_the_author_meta( 'ID' );
            $author_name = get_the_author();
            $status = get_post_status( $post_id );
            
            $status_labels = array(
                'publish' => 'Publicado',
                'draft' => 'Borrador',
                'pending' => 'Pendiente',
                'future' => 'Programado'
            );
            $status_label = isset( $status_labels[ $status ] ) ? $status_labels[ $status ] : $status;
            
            echo '<li class="pro-post-item ' . esc_attr( $status ) . '">';
            echo '<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">';
            echo '<div>';
            echo '<a href="' . esc_url( get_edit_post_link( $post_id ) ) . '" class="pro-post-title-link"><strong>' . esc_html( get_the_title() ) . '</strong></a>';
            echo '<div class="pro-post-meta" style="margin-top:4px;">';
            if ( $is_admin || $is_director ) {
                echo '<span class="pro-post-meta-author">' . get_avatar( $author_id, 16 ) . ' ' . esc_html( $author_name ) . '</span> &bull; ';
            }
            echo '<span>Modificado ' . esc_html( pro_get_human_time_diff( get_the_modified_date( 'Y-m-d H:i:s' ) ) ) . '</span>';
            echo '</div>';
            echo '</div>';
            echo '<div><span class="pro-post-status-badge ' . esc_attr( $status ) . '">' . esc_html( $status_label ) . '</span></div>';
            echo '</div>';
            echo '</li>';
        }
        echo '</ul>';
        wp_reset_postdata();
        
        echo '<div class="pro-widget-actions" style="margin-top:15px;">';
        echo '<a href="' . esc_url( admin_url( 'edit.php' ) ) . '" class="button"><span class="dashicons dashicons-admin-post"></span> Ver Todas las Entradas</a>';
        echo '</div>';
    } else {
        echo '<div class="pro-empty-state"><span class="dashicons dashicons-admin-post"></span><p>No se encontraron publicaciones publicadas o redactadas recientemente.</p></div>';
    }
    echo '</div>';
}

// WIDGET 5: Actividad y Sesiones de Autores
function pro_render_dashboard_actividad_autores_widget() {
    $current_user = wp_get_current_user();
    $is_admin = in_array( 'administrator', $current_user->roles );
    $is_director = in_array( 'direccion', $current_user->roles ) || in_array( 'gerencia', $current_user->roles ) || in_array( 'publicista', $current_user->roles );
    
    echo '<div class="pro-widget-container activity">';
    
    if ( $is_admin || $is_director ) {
        // Vista para Dirección y Administración: Tabla General de todos los redactores y directores
        $users_query = new WP_User_Query( array(
            'role__in' => array( 'autor', 'author', 'direccion' ),
            'number'   => -1,
            'orderby'  => 'display_name',
            'order'    => 'ASC'
        ) );
        
        $authors = $users_query->get_results();
        
        if ( ! empty( $authors ) ) {
            echo '<p style="color:#64748b; font-size:11px; margin-top:0; margin-bottom:15px;"><span class="dashicons dashicons-info" style="font-size:14px; width:14px; height:14px; margin-top:-2px;"></span> Actividad de los redactores y directores durante la fecha de hoy.</p>';
            echo '<table class="wp-list-table widefat fixed striped pro-activity-table">';
            echo '<thead><tr><th>Autor / Cargo</th><th>Ingreso de Hoy</th><th>Última Sesión</th><th>Último Post</th></tr></thead>';
            echo '<tbody>';
            
            $today = current_time( 'Y-m-d' );
            
            foreach ( $authors as $author ) {
                $last_login = get_user_meta( $author->ID, '_pro_last_login_time', true );
                $first_login = get_user_meta( $author->ID, '_pro_first_login_today', true );
                
                // Determinar si ingresó hoy
                $is_active_today = false;
                if ( ! empty( $first_login ) && substr( $first_login, 0, 10 ) === $today ) {
                    $is_active_today = true;
                }
                
                $status_dot = $is_active_today 
                    ? '<span class="pro-status-dot active" title="Conectado hoy"></span>' 
                    : '<span class="pro-status-dot offline" title="Sin conexión hoy"></span>';
                
                // Obtener última publicación publicada del usuario
                $last_post_query = new WP_Query( array(
                    'author'         => $author->ID,
                    'post_type'      => 'post',
                    'post_status'    => 'publish',
                    'posts_per_page' => 1,
                ) );
                
                $last_post_text = 'Sin publicaciones';
                if ( $last_post_query->have_posts() ) {
                    $last_post_query->the_post();
                    $post_time = get_the_date( 'Y-m-d H:i:s' );
                    $last_post_text = '<a href="' . esc_url( get_permalink() ) . '" target="_blank" title="' . esc_attr( get_the_title() ) . '">' . esc_html( pro_get_human_time_diff( $post_time ) ) . '</a>';
                }
                wp_reset_postdata();
                
                $first_login_time = 'Sin ingresos';
                if ( $is_active_today ) {
                    $first_login_time = date_i18n( 'g:i a', strtotime( $first_login ) );
                }
                
                $last_login_formatted = ! empty( $last_login ) ? pro_get_human_time_diff( $last_login ) : 'Nunca';
                
                // Pill de Rol
                $role_badge = '';
                if ( in_array( 'direccion', $author->roles ) || in_array( 'publicista', $author->roles ) ) {
                    $role_badge = '<span class="pro-role-pill director">Dirección</span>';
                } else {
                    $role_badge = '<span class="pro-role-pill autor">Autor</span>';
                }
                
                echo '<tr>';
                echo '<td><div style="display:flex; align-items:center; gap:8px;">' . $status_dot . get_avatar( $author->ID, 24 ) . '<div><strong>' . esc_html( $author->display_name ) . '</strong><br/>' . $role_badge . '</div></div></td>';
                echo '<td>' . esc_html( $first_login_time ) . '</td>';
                echo '<td>' . esc_html( $last_login_formatted ) . '</td>';
                echo '<td>' . $last_post_text . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
        } else {
            echo '<div class="pro-empty-state"><span class="dashicons dashicons-admin-users"></span><p>No se encontraron redactores registrados.</p></div>';
        }
    } else {
        // Vista para Autores: Tarjeta personalizada interactiva de resumen
        $last_login = get_user_meta( $current_user->ID, '_pro_last_login_time', true );
        $first_login = get_user_meta( $current_user->ID, '_pro_first_login_today', true );
        
        $last_post_query = new WP_Query( array(
            'author'         => $current_user->ID,
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
        ) );
        
        $last_post_text = 'Aún no has publicado ningún artículo.';
        if ( $last_post_query->have_posts() ) {
            $last_post_query->the_post();
            $post_time = get_the_date( 'Y-m-d H:i:s' );
            $last_post_text = '<strong><a href="' . esc_url( get_permalink() ) . '" target="_blank">' . esc_html( get_the_title() ) . '</a></strong><br/><span style="color:#64748b; font-size:11px;">Publicado ' . esc_html( pro_get_human_time_diff( $post_time ) ) . '</span>';
        }
        wp_reset_postdata();
        
        $today = current_time( 'Y-m-d' );
        $first_login_time = 'Sin registro';
        if ( ! empty( $first_login ) && substr( $first_login, 0, 10 ) === $today ) {
            $first_login_time = date_i18n( 'g:i a', strtotime( $first_login ) );
        }
        
        $last_login_formatted = ! empty( $last_login ) ? date_i18n( 'g:i a', strtotime( $last_login ) ) : 'Sin registro';
        
        echo '<div class="pro-author-personal-card">';
        echo '<div class="pro-author-card-header">';
        echo get_avatar( $current_user->ID, 48 );
        echo '<div>';
        echo '<h3 style="margin:0; font-weight:700; color:#0f172a; font-size:16px;">¡Hola, ' . esc_html( $current_user->display_name ) . '!</h3>';
        echo '<span class="pro-role-pill autor" style="margin-top:4px; display:inline-block;">Autor (Redacción)</span>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="pro-author-stats-grid">';
        echo '<div class="pro-author-stat-item">';
        echo '<div class="pro-stat-icon" style="background:rgba(59, 130, 246, 0.1); color:#3b82f6;"><span class="dashicons dashicons-clock"></span></div>';
        echo '<div><span class="pro-stat-label">Primer Ingreso Diario</span><span class="pro-stat-value">' . esc_html( $first_login_time ) . '</span></div>';
        echo '</div>';
        echo '<div class="pro-author-stat-item">';
        echo '<div class="pro-stat-icon" style="background:rgba(16, 185, 129, 0.1); color:#10b981;"><span class="dashicons dashicons-migrate"></span></div>';
        echo '<div><span class="pro-stat-label">Última Sesión</span><span class="pro-stat-value">' . esc_html( $last_login_formatted ) . '</span></div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="pro-author-last-publication">';
        echo '<h4 style="margin-top:0; margin-bottom:8px; font-weight:700; color:#1e293b; font-size:13px; display:flex; align-items:center; gap:6px;"><span class="dashicons dashicons-admin-post" style="color:#64748b;"></span> Tu Última Publicación:</h4>';
        echo '<p style="margin:0; line-height:1.5;">' . $last_post_text . '</p>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
}

/**
 * 4. ENDPOINTS AJAX PARA LA GESTIÓN DE CLIENTES ACTIVER (ADMINISTRACIÓN)
 */
function pro_ajax_add_client() {
    check_ajax_referer( 'pro_client_manager_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Acceso denegado: No tienes privilegios administrativos.' ) );
    }
    
    $name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
    $plan = isset( $_POST['plan'] ) ? sanitize_text_field( $_POST['plan'] ) : '';
    $cost = isset( $_POST['cost'] ) ? sanitize_text_field( $_POST['cost'] ) : '';
    $cutoff = isset( $_POST['cutoff'] ) ? sanitize_text_field( $_POST['cutoff'] ) : '';
    $paid = isset( $_POST['paid'] ) && $_POST['paid'] === 'true' ? 'paid' : 'pending';
    
    if ( empty( $name ) || empty( $cutoff ) ) {
        wp_send_json_error( array( 'message' => 'Por favor completa el nombre del cliente y la fecha de corte.' ) );
    }
    
    $clients = get_option( 'pro_active_clients', array() );
    
    // Obtener ID correlativo más alto
    $max_id = 0;
    foreach ( $clients as $c ) {
        if ( isset( $c['id'] ) && $c['id'] > $max_id ) {
            $max_id = $c['id'];
        }
    }
    $new_id = $max_id + 1;
    
    $new_client = array(
        'id'      => $new_id,
        'name'    => $name,
        'plan'    => $plan,
        'cost'    => $cost,
        'status'  => $paid,
        'cut_off' => $cutoff
    );
    
    $clients[] = $new_client;
    update_option( 'pro_active_clients', $clients );
    
    $cutoff_formatted = date_i18n( 'j \d\e F, Y', strtotime( $cutoff ) );
    $status_class = ( $paid === 'paid' ) ? 'paid' : 'pending';
    $status_label = ( $paid === 'paid' ) ? 'PAGADO' : 'PENDIENTE';
    
    wp_send_json_success( array(
        'client' => $new_client,
        'html'   => '<tr id="pro-client-row-' . esc_attr( $new_id ) . '"><td><strong>' . esc_html( $name ) . '</strong><br/><span style="color:#94a3b8; font-size:10px;">' . esc_html( $cost ) . '</span></td><td>' . esc_html( $plan ) . '</td><td>' . esc_html( $cutoff_formatted ) . '</td><td><span class="pro-client-status-badge ' . esc_attr( $status_class ) . '">' . esc_html( $status_label ) . '</span></td><td style="text-align: center;"><button type="button" class="pro-btn-delete-client" data-id="' . esc_attr( $new_id ) . '" title="Eliminar Cliente"><span class="dashicons dashicons-trash"></span></button></td></tr>'
    ) );
}
add_action( 'wp_ajax_pro_add_client', 'pro_ajax_add_client' );

function pro_ajax_delete_client() {
    check_ajax_referer( 'pro_client_manager_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Acceso denegado: No tienes privilegios administrativos.' ) );
    }
    
    $client_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
    if ( ! $client_id ) {
        wp_send_json_error( array( 'message' => 'Identificador de cliente no válido.' ) );
    }
    
    $clients = get_option( 'pro_active_clients', array() );
    $filtered_clients = array();
    
    foreach ( $clients as $c ) {
        if ( isset( $c['id'] ) && $c['id'] !== $client_id ) {
            $filtered_clients[] = $c;
        }
    }
    
    update_option( 'pro_active_clients', $filtered_clients );
    wp_send_json_success( array( 'message' => 'Cliente eliminado correctamente.' ) );
}
add_action( 'wp_ajax_pro_delete_client', 'pro_ajax_delete_client' );

/**
 * 5. INYECCIÓN DE ESTILOS CSS Y SCRIPTS JQUERY/AJAX PARA LOS WIDGETS
 */
function pro_dashboard_widgets_styles() {
    global $pagenow;
    if ( 'index.php' !== $pagenow ) {
        return;
    }
    ?>
    <style>
        /* Contenedores de Widgets */
        .pro-widget-container {
            margin: -12px;
            padding: 15px;
            background: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .pro-empty-state {
            text-align: center;
            padding: 30px 15px;
            color: #94a3b8;
        }
        .pro-empty-state .dashicons {
            font-size: 36px;
            width: 36px;
            height: 36px;
            margin-bottom: 8px;
            color: #cbd5e1;
        }
        .pro-empty-state p {
            margin: 0;
            font-size: 13px;
        }
        
        /* Widget Portada */
        .pro-portada-preview-container {
            position: relative;
            max-width: 200px;
            margin: 0 auto 15px auto;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .pro-portada-preview-img {
            width: 100%;
            height: auto;
            display: block;
            max-height: 280px;
            object-fit: contain;
        }
        .pro-portada-status-tag {
            position: absolute;
            bottom: 8px;
            left: 8px;
            right: 8px;
            background: rgba(15, 23, 42, 0.85);
            color: #ffffff;
            text-align: center;
            padding: 4px;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-radius: 4px;
            backdrop-filter: blur(2px);
        }
        .pro-portada-scheduled-banner {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(59, 130, 246, 0.08);
            color: #1e3a8a;
            border: 1px solid rgba(59, 130, 246, 0.15);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        .pro-portada-scheduled-banner .dashicons {
            color: #3b82f6;
            font-size: 18px;
            width: 18px;
            height: 18px;
        }
        .pro-portada-scheduled-banner strong {
            font-size: 12px;
        }
        .pro-portada-scheduled-banner p {
            margin: 2px 0 0 0;
            font-size: 10px;
            color: #475569;
        }
        
        /* Widget Clientes */
        .pro-clients-table th {
            font-weight: 700 !important;
            color: #334155;
            font-size: 12px;
        }
        .pro-client-status-badge {
            display: inline-block;
            padding: 3px 6px;
            font-size: 8px;
            font-weight: 700;
            border-radius: 6px;
        }
        .pro-client-status-badge.paid {
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
        }
        .pro-client-status-badge.pending {
            background: rgba(245, 158, 11, 0.12);
            color: #d97706;
        }
        tr.pro-alert-danger {
            background: #fff8f8 !important;
        }
        tr.pro-alert-danger td {
            border-left: 3px solid #ef4444 !important;
        }
        .pro-btn-delete-client {
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.15s ease;
        }
        .pro-btn-delete-client:hover {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.08);
        }
        .pro-add-client-toggle-container {
            margin-top: 12px;
            text-align: right;
        }
        
        /* Widget Leads */
        .pro-leads-feed {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .pro-lead-item {
            border-bottom: 1px solid #f1f5f9;
            padding: 10px 0;
        }
        .pro-lead-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .pro-lead-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 3px;
        }
        .pro-lead-header strong {
            font-size: 12px;
            color: #0f172a;
        }
        .pro-lead-date {
            font-size: 9px;
            color: #94a3b8;
        }
        .pro-lead-meta {
            display: flex;
            gap: 10px;
            font-size: 9px;
            color: #64748b;
            margin-bottom: 5px;
        }
        .pro-lead-meta-item {
            display: flex;
            align-items: center;
            gap: 3px;
        }
        .pro-lead-meta-item .dashicons {
            font-size: 11px;
            width: 11px;
            height: 11px;
        }
        .pro-lead-excerpt {
            margin: 0;
            font-size: 11px;
            color: #475569;
            line-height: 1.4;
        }
        
        /* Widget Publicaciones */
        .pro-posts-feed {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .pro-post-item {
            border-bottom: 1px solid #f1f5f9;
            padding: 9px 0;
        }
        .pro-post-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .pro-post-title-link {
            color: #1e293b;
            text-decoration: none;
            font-size: 12px;
            transition: color 0.15s ease;
        }
        .pro-post-title-link:hover {
            color: #3b82f6;
        }
        .pro-post-meta {
            font-size: 9px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .pro-post-meta img {
            border-radius: 50%;
            vertical-align: middle;
        }
        .pro-post-status-badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 8px;
            font-weight: 700;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .pro-post-status-badge.publish {
            background: rgba(16, 185, 129, 0.08);
            color: #10b981;
        }
        .pro-post-status-badge.draft {
            background: rgba(100, 116, 139, 0.08);
            color: #64748b;
        }
        .pro-post-status-badge.pending {
            background: rgba(245, 158, 11, 0.08);
            color: #f59e0b;
        }
        .pro-post-status-badge.future {
            background: rgba(59, 130, 246, 0.08);
            color: #3b82f6;
        }
        
        /* Widget Actividad */
        .pro-activity-table th {
            font-weight: 700 !important;
            color: #334155;
            font-size: 12px;
        }
        .pro-status-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .pro-status-dot.active {
            background: #10b981;
            box-shadow: 0 0 6px #10b981;
        }
        .pro-status-dot.offline {
            background: #cbd5e1;
        }
        .pro-role-pill {
            display: inline-block;
            padding: 1px 4px;
            font-size: 7.5px;
            font-weight: 700;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .pro-role-pill.director {
            background: rgba(127, 29, 29, 0.08);
            color: #7f1d1d;
        }
        .pro-role-pill.autor {
            background: rgba(6, 95, 70, 0.08);
            color: #065f46;
        }
        
        /* Ficha Autor Personal */
        .pro-author-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .pro-author-card-header img {
            border-radius: 50%;
            border: 2px solid #f1f5f9;
        }
        .pro-author-stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }
        .pro-author-stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #f1f5f9;
        }
        .pro-stat-icon {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pro-stat-icon .dashicons {
            font-size: 14px;
            width: 14px;
            height: 14px;
        }
        .pro-stat-label {
            display: block;
            font-size: 8px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .pro-stat-value {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
        }
        .pro-author-last-publication {
            background: rgba(248, 250, 252, 0.5);
            border: 1px dashed #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
        }
        
        .pro-widget-actions {
            border-top: 1px solid #f1f5f9;
            padding-top: 12px;
            text-align: right;
        }
        .pro-widget-actions .button {
            font-weight: 600 !important;
            height: 28px !important;
            line-height: 26px !important;
        }
    </style>
    <?php
}
add_action( 'admin_head', 'pro_dashboard_widgets_styles' );

function pro_dashboard_widgets_scripts() {
    global $pagenow;
    if ( 'index.php' !== $pagenow ) {
        return;
    }
    
    $nonce = wp_create_nonce( 'pro_client_manager_nonce' );
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Desplegar/Colapsar formulario
            $('#pro-toggle-add-client-form').on('click', function() {
                $('#pro-add-client-form-container').slideToggle(200);
            });
            
            $('#pro-cancel-client-btn').on('click', function() {
                $('#pro-add-client-form-container').slideUp(200);
            });
            
            // Agregar cliente AJAX
            $('#pro-submit-client-btn').on('click', function() {
                var name = $('#pro-new-client-name').val();
                var plan = $('#pro-new-client-plan').val();
                var cost = $('#pro-new-client-cost').val();
                var cutoff = $('#pro-new-client-cutoff').val();
                var paid = $('#pro-new-client-paid').is(':checked');
                
                if (!name || !cutoff) {
                    alert('Por favor, ingresa el nombre del cliente y la fecha de corte.');
                    return;
                }
                
                var btn = $(this);
                btn.prop('disabled', true).text('Guardando...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pro_add_client',
                        nonce: '<?php echo esc_js($nonce); ?>',
                        name: name,
                        plan: plan,
                        cost: cost,
                        cutoff: cutoff,
                        paid: paid
                    },
                    success: function(response) {
                        btn.prop('disabled', false).text('Guardar');
                        if (response.success) {
                            $('#pro-clients-list-body').append(response.data.html);
                            $('#pro-new-client-name').val('');
                            $('#pro-add-client-form-container').slideUp(200);
                        } else {
                            alert(response.data.message);
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).text('Guardar');
                        alert('Hubo un error de comunicación.');
                    }
                });
            });
            
            // Eliminar cliente AJAX
            $(document).on('click', '.pro-btn-delete-client', function() {
                var btn = $(this);
                var clientId = btn.data('id');
                
                if (!confirm('¿Estás seguro de que deseas eliminar este cliente?')) {
                    return;
                }
                
                btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="animation: spin 1s infinite linear; font-size:14px; width:14px; height:14px; margin-top:-1px;"></span>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pro_delete_client',
                        nonce: '<?php echo esc_js($nonce); ?>',
                        id: clientId
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#pro-client-row-' + clientId).fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span>');
                            alert(response.data.message);
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span>');
                        alert('Error al eliminar cliente.');
                    }
                });
            });
        });
    </script>
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
    <?php
}
add_action( 'admin_footer', 'pro_dashboard_widgets_scripts' );

/**
 * ==============================================================
 * VALIDACIÓN Y OBLIGATORIEDAD DE FIRMA EN PUBLICACIONES
 * ==============================================================
 */

/**
 * 1. VALIDADOR FRONT-END (JavaScript):
 * Intercepta los intentos de hacer click en "Publicar" tanto en Gutenberg como en el Editor Clásico.
 * Si el campo de firma está vacío, bloquea la acción, muestra una alerta premium y enfoca el campo de firma.
 */
function pro_firma_validation_admin_footer_scripts() {
    global $pagenow;
    if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
        return;
    }
    if ( get_post_type() !== 'post' ) {
        return;
    }
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // A. Validación para Editor Clásico (Submit clásico)
            $('#post').on('submit', function(e) {
                if (document.activeElement && document.activeElement.id === 'publish') {
                    var signature = $('#pro_firma_autor_field').val();
                    if (!signature || signature.trim() === '') {
                        e.preventDefault();
                        alert('⚠️ ERROR DE PUBLICACIÓN:\n\nEs obligatorio firmar la publicación para poder hacerla pública.\n\nPor favor, escribe tu nombre en la caja "Firma" antes de continuar.');
                        $('#pro_firma_autor_field').focus().css({
                            'border': '2px solid #ef4444',
                            'background-color': '#fff5f5'
                        });
                        return false;
                    }
                }
            });

            // B. Validación para Editor Gutenberg (Block Editor)
            // Escuchar clics en el botón de abrir panel y en el de publicar definitivo
            $(document).on('click', '.editor-post-publish-panel__toggle, .editor-post-publish-button, button.editor-post-publish-button__button', function(e) {
                var signature = $('#pro_firma_autor_field').val();
                if (!signature || signature.trim() === '') {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    alert('⚠️ ERROR DE PUBLICACIÓN:\n\nEs obligatorio firmar la publicación para poder hacerla pública.\n\nPor favor, escribe tu nombre en la caja "Firma" antes de continuar.');
                    
                    // Resaltar visualmente el campo de firma vacío
                    $('#pro_firma_autor_field').focus().css({
                        'border': '2px solid #ef4444',
                        'background-color': '#fff5f5',
                        'box-shadow': '0 0 5px rgba(239, 68, 68, 0.5)'
                    });
                    
                    // Hacer scroll hacia el metabox de firma para que el usuario lo vea
                    var $metabox = $('#pro_firma_autor_metabox');
                    if ($metabox.length) {
                        $('html, body').animate({
                            scrollTop: $metabox.offset().top - 120
                        }, 300);
                    }
                    
                    return false;
                }
            });
        });
    </script>
    <?php
}
add_action( 'admin_footer', 'pro_firma_validation_admin_footer_scripts' );

/**
 * 2. VALIDADOR BACK-END (PHP - Red de Seguridad Nuclear):
 * Intercepta el guardado en la base de datos. Si se intenta publicar/programar un post
 * sin firma, revierte su estado automáticamente a Borrador y crea un transitorio de error.
 */
function pro_enforce_firma_on_publish( $data, $postarr ) {
    // Aplicar únicamente al post_type 'post'
    if ( $data['post_type'] !== 'post' ) {
        return $data;
    }

    // Comprobar si el estado destino es público ('publish') o programado ('future')
    if ( in_array( $data['post_status'], array( 'publish', 'future' ), true ) ) {
        $post_id = isset( $postarr['ID'] ) ? $postarr['ID'] : 0;
        $firma = '';

        // 1. Buscar firma en la petición POST clásica
        if ( isset( $_POST['pro_firma_autor_field'] ) ) {
            $firma = sanitize_text_field( $_POST['pro_firma_autor_field'] );
        }
        // 2. Si no viene en $_POST (REST API / Gutenberg), consultar meta actual guardado
        elseif ( $post_id ) {
            $firma = get_post_meta( $post_id, '_pro_firma_autor', true );
        }

        // 3. Soporte adicional para peticiones crudas REST de metadatos
        if ( empty( $firma ) && isset( $_POST['meta']['_pro_firma_autor'] ) ) {
            $firma = sanitize_text_field( $_POST['meta']['_pro_firma_autor'] );
        }

        // Si la firma está completamente vacía
        if ( empty( trim( $firma ) ) ) {
            // Revertir el estado destino al estado previo (borrador o pendiente)
            $original_status = isset( $postarr['original_post_status'] ) ? $postarr['original_post_status'] : 'draft';
            $data['post_status'] = in_array( $original_status, array( 'draft', 'pending' ) ) ? $original_status : 'draft';

            // Sembrar transitorio temporal para gatillar el aviso de error en la recarga del administrador
            if ( $post_id ) {
                set_transient( 'pro_firma_error_notice_' . $post_id, true, 45 );
            }
        }
    }
    return $data;
}
add_filter( 'wp_insert_post_data', 'pro_enforce_firma_on_publish', 10, 2 );

/**
 * 3. AVISO ADMINISTRATIVO DE ERROR (PHP):
 * Si se activó la red de seguridad del back-end, despliega una alerta roja premium y explicativa en la pantalla de edición.
 */
function pro_firma_error_admin_notices() {
    global $pagenow;
    if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
        return;
    }
    
    $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
    if ( ! $post_id ) {
        return;
    }

    if ( get_transient( 'pro_firma_error_notice_' . $post_id ) ) {
        delete_transient( 'pro_firma_error_notice_' . $post_id );
        ?>
        <div class="notice notice-error is-dismissible" style="border-left-color: #ef4444; border-left-width: 4px; background-color: #fffbfa; padding: 12px 18px; margin: 20px 0 10px 0; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <p style="font-size: 13px; line-height: 1.6; color: #1e293b; margin: 0; padding: 0;">
                <strong style="color: #ef4444; font-size: 15px; display: block; margin-bottom: 5px; font-weight: 700;">⚠️ Entrada no Publicada - Falta Firma Obligatoria</strong>
                Es **estrictamente obligatorio** firmar la publicación con tu nombre de autor para poder hacerla pública en el portal. 
                El estado de esta entrada ha sido **revertido automáticamente a Borrador**.
                <br/><br/>
                Por favor, escribe tu nombre en el casillero **Firma** (ubicado en la barra lateral derecha o inferior) y vuelve a intentar la publicación.
            </p>
        </div>
        <?php
    }
}
add_action( 'admin_notices', 'pro_firma_error_admin_notices' );




/**
 * Desactivar comentarios en todo el tema
 */
add_action('admin_init', function () {
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);
add_filter('comments_array', '__return_empty_array', 10, 2);

add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

add_action('init', function () {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
}, 9999);

/**
 * Auto-actualizador para la solicitud del usuario (Seguridad y Zona deportiva)
 */
function pro_apply_user_updates() {
    if ( get_option( 'pro_user_updates_applied_v2' ) ) {
        return;
    }

    // 1. Crear o asegurar Categoría "Seguridad"
    if ( ! term_exists( 'Seguridad', 'category' ) ) {
        wp_insert_term( 'Seguridad', 'category' );
    }

    // 2. Crear o asegurar Página "Seguridad"
    $seguridad_page = get_page_by_title( 'Seguridad' );
    if ( ! $seguridad_page ) {
        $seguridad_page_id = wp_insert_post( array(
            'post_title'     => 'Seguridad',
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'post_author'    => 1,
        ) );
        if ( $seguridad_page_id && ! is_wp_error( $seguridad_page_id ) ) {
            update_post_meta( $seguridad_page_id, '_wp_page_template', 'page-categoria.php' );
            $seguridad_page = get_post( $seguridad_page_id );
        }
    } else {
        update_post_meta( $seguridad_page->ID, '_wp_page_template', 'page-categoria.php' );
    }

    // 3. Crear Categoría "Zona deportiva"
    $zona_deportiva_term = term_exists( 'Zona deportiva', 'category' );
    if ( ! $zona_deportiva_term ) {
        $zona_deportiva_term = wp_insert_term( 'Zona deportiva', 'category' );
    }

    // 4. Actualizar el menú "Menú Principal Nuclear"
    $menu_name = 'Menú Principal Nuclear';
    $menu_exists = wp_get_nav_menu_object( $menu_name );
    
    if ( $menu_exists && $seguridad_page ) {
        $menu_id = $menu_exists->term_id;
        $menu_items = wp_get_nav_menu_items( $menu_id );
        
        $deportes_menu_item_id = 0;
        $has_seguridad = false;
        $has_zona_deportiva = false;
        $mas_menu_item_id = 0;

        foreach ( $menu_items as $item ) {
            if ( $item->title === 'Deportes' ) {
                $deportes_menu_item_id = $item->ID;
            }
            if ( $item->title === 'Seguridad' ) {
                $has_seguridad = true;
            }
            if ( $item->title === 'Zona deportiva' ) {
                $has_zona_deportiva = true;
            }
            if ( $item->title === 'Más' ) {
                $mas_menu_item_id = $item->ID;
            }
        }

        // Agregar página "Seguridad" si no está en el menú (dentro de "Más")
        if ( ! $has_seguridad ) {
            wp_update_nav_menu_item( $menu_id, 0, array(
                'menu-item-title'     => 'Seguridad',
                'menu-item-object-id' => $seguridad_page->ID,
                'menu-item-object'    => 'page',
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
                'menu-item-parent-id' => $mas_menu_item_id ? $mas_menu_item_id : 0,
            ) );
        }

        // Agregar categoría "Zona deportiva" bajo "Deportes" si no está
        if ( ! $has_zona_deportiva && $deportes_menu_item_id && ! is_wp_error( $zona_deportiva_term ) ) {
            $term_id = is_array( $zona_deportiva_term ) ? $zona_deportiva_term['term_id'] : $zona_deportiva_term;
            wp_update_nav_menu_item( $menu_id, 0, array(
                'menu-item-title'     => 'Zona deportiva',
                'menu-item-object-id' => $term_id,
                'menu-item-object'    => 'category',
                'menu-item-type'      => 'taxonomy',
                'menu-item-status'    => 'publish',
                'menu-item-parent-id' => $deportes_menu_item_id,
            ) );
        }
    }

    update_option( 'pro_user_updates_applied_v2', true );
}
add_action( 'admin_init', 'pro_apply_user_updates' );



// ==========================================

function pro_fix_corrupted_terms() {
    if ( get_option( 'pro_terms_encoding_fixed_v1' ) ) {
        return;
    }

    $taxonomies = get_taxonomies();
    foreach ( $taxonomies as $taxonomy ) {
        $terms = get_terms( array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ) );

        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                if ( strpos( $term->name, 'Ã' ) !== false ) {
                    $correct_name = mb_convert_encoding( $term->name, 'ISO-8859-1', 'UTF-8' );
                    
                    $existing_term = term_exists( $correct_name, $taxonomy, $term->parent );
                    
                    if ( $existing_term && $existing_term['term_id'] != $term->term_id ) {
                        $posts = get_objects_in_term( $term->term_id, $taxonomy );
                        if ( ! is_wp_error( $posts ) && ! empty( $posts ) ) {
                            foreach ( $posts as $post_id ) {
                                wp_set_object_terms( $post_id, (int) $existing_term['term_id'], $taxonomy, true );
                            }
                        }
                        wp_delete_term( $term->term_id, $taxonomy );
                    } else {
                        wp_update_term( $term->term_id, $taxonomy, array(
                            'name' => $correct_name,
                            'slug' => sanitize_title( $correct_name )
                        ) );
                    }
                }
            }
        }
    }
    
    update_option( 'pro_terms_encoding_fixed_v1', true );
}
add_action( 'init', 'pro_fix_corrupted_terms' );


function pro_setup_espressivo_categories_and_menu() {
    if ( get_option( 'pro_espressivo_structure_installed_v19' ) ) {
        return;
    }
    update_option( 'pro_espressivo_structure_installed_v19', true ); // Lock early to prevent concurrent executions


    $estructura = array(
        'Monagas' => array('description' => '', 'children' => array('Ciudad', 'Comunidad', 'Política Monagas', 'Sucesos Monagas', 'Educación', 'Salud', 'Municipios')),
        'Sucesos' => array('description' => '', 'children' => array('Seguridad', 'Crónica Policial')),
        'Nacional' => array('description' => '', 'children' => array('Regiones', 'Política Nacional', 'Asamblea Nacional', 'Economía')),
        'Mundo' => array('description' => '', 'children' => array('Análisis Internacional', 'Bitácora Mundial')),
        'Deportes' => array('description' => '', 'children' => array()),
        'Artes y Espectáculos' => array('description' => '', 'children' => array('Farándula', 'Cine', 'Streaming', 'Cultura', 'Literatura')),
        'Bienestar' => array('description' => '', 'children' => array('Nueva Salud', 'Gastronomía', 'Belleza', 'Viajes', 'Estilo de vida', 'Mascotas')),
        'Ciencia y Tecnología' => array('description' => '', 'children' => array('Inteligencia Artificial', 'Robótica')),
        'Opinión' => array('description' => '', 'children' => array()),
        'Buen Ciudadano' => array('description' => '', 'children' => array()),
        'Edictos y Carteles' => array('description' => '', 'children' => array()),
        'Portafolio' => array('description' => '', 'children' => array())
    );

    $cat_ids = array();
    foreach ( $estructura as $parent => $data ) {
        $parent_term = term_exists( $parent, 'category' );
        if ( ! $parent_term ) { $parent_term = wp_insert_term( $parent, 'category', array('description' => $data['description']) ); }
        if ( ! is_wp_error( $parent_term ) ) {
            $parent_id = is_array( $parent_term ) ? $parent_term['term_id'] : $parent_term;
            $cat_ids[$parent] = array( 'id' => $parent_id, 'children' => array() );
            foreach ( $data['children'] as $child ) {
                $child_term = term_exists( $child, 'category', $parent_id );
                if ( ! $child_term ) { 
                    $child_term = wp_insert_term( $child, 'category', array('parent' => $parent_id) ); 
                } 
                if ( ! is_wp_error( $child_term ) ) { 
                    $cat_ids[$parent]['children'][$child] = is_array( $child_term ) ? $child_term['term_id'] : $child_term; 
                }
            }
        }
    }

    foreach ( $estructura as $parent => $data ) {
        if ( $parent === 'Edictos y Carteles' ) continue;
        
        $page = get_page_by_title($parent) ?: get_page_by_path(sanitize_title($parent));
        if ( ! $page ) {
            $page_id = wp_insert_post( array('post_title'=>$parent,'post_name'=>sanitize_title($parent),'post_status'=>'publish','post_type'=>'page','post_author'=>1) );
            if ( $page_id && ! is_wp_error( $page_id ) ) { update_post_meta( $page_id, '_wp_page_template', 'page-categoria.php' ); }
        }
        
        foreach ( $data['children'] as $child ) {
            $child_page = get_page_by_title($child) ?: get_page_by_path(sanitize_title($child));
            if ( ! $child_page ) {
                $child_page_id = wp_insert_post( array('post_title'=>$child,'post_name'=>sanitize_title($child),'post_status'=>'publish','post_type'=>'page','post_author'=>1) );
                if ( $child_page_id && ! is_wp_error( $child_page_id ) ) { update_post_meta( $child_page_id, '_wp_page_template', 'page-categoria.php' ); }
            }
        }
    }

    $carteles_page = get_page_by_path('edictos-y-carteles') ?: get_page_by_title('Carteles y Edictos');
    if ( ! $carteles_page ) {
        $page_id = wp_insert_post( array('post_title'=>'Carteles y Edictos','post_name'=>'edictos-y-carteles','post_status'=>'publish','post_type'=>'page','post_author'=>1) );
        if ( $page_id && ! is_wp_error( $page_id ) ) { update_post_meta( $page_id, '_wp_page_template', 'page-carteles.php' ); }
    }

    // MENU REFACTOR: Split Desktop and Mobile into separate menus
    $menu_name = 'Menú Espressivo';
    $menu_exists = wp_get_nav_menu_object( $menu_name );
    if ( $menu_exists ) { wp_delete_nav_menu( $menu_name ); }
    $menu_id = wp_create_nav_menu( $menu_name );

    $mobile_menu_name = 'Menu e-movil';
    $mobile_menu_exists = wp_get_nav_menu_object( $mobile_menu_name );
    if ( $mobile_menu_exists ) { wp_delete_nav_menu( $mobile_menu_name ); }
    $mobile_menu_id = wp_create_nav_menu( $mobile_menu_name );

    if ( $menu_id && $mobile_menu_id ) {
        wp_update_nav_menu_item( $menu_id, 0, array('menu-item-title'=>'Inicio','menu-item-url'=>home_url( '/' ),'menu-item-status'=>'publish','menu-item-type'=>'custom') );
        wp_update_nav_menu_item( $mobile_menu_id, 0, array('menu-item-title'=>'Inicio','menu-item-url'=>home_url( '/' ),'menu-item-status'=>'publish','menu-item-type'=>'custom') );

        $principales = array('Monagas', 'Sucesos', 'Nacional', 'Mundo', 'Deportes', 'Buen Ciudadano');
        $secundarias = array('Edictos y Carteles', 'Portafolio', 'Artes y Espectáculos', 'Ciencia y Tecnología', 'Opinión', 'Bienestar');
        
        foreach ( $principales as $parent ) {
            $page = get_page_by_title($parent) ?: get_page_by_path(sanitize_title($parent));
            if ( $page ) {
                $parent_item_id_desktop = wp_update_nav_menu_item( $menu_id, 0, array('menu-item-title'=>$parent,'menu-item-object-id'=>$page->ID,'menu-item-object'=>'page','menu-item-type'=>'post_type','menu-item-status'=>'publish') );
                $parent_item_id_mobile = wp_update_nav_menu_item( $mobile_menu_id, 0, array('menu-item-title'=>$parent,'menu-item-object-id'=>$page->ID,'menu-item-object'=>'page','menu-item-type'=>'post_type','menu-item-status'=>'publish') );
                
                foreach ( $estructura[$parent]['children'] as $child ) {
                    $child_page = get_page_by_title($child) ?: get_page_by_path(sanitize_title($child));
                    if ( $child_page ) {
                        wp_update_nav_menu_item( $menu_id, 0, array('menu-item-title'=>$child,'menu-item-object-id'=>$child_page->ID,'menu-item-object'=>'page','menu-item-type'=>'post_type','menu-item-status'=>'publish','menu-item-parent-id'=>$parent_item_id_desktop) );
                        wp_update_nav_menu_item( $mobile_menu_id, 0, array('menu-item-title'=>$child,'menu-item-object-id'=>$child_page->ID,'menu-item-object'=>'page','menu-item-type'=>'post_type','menu-item-status'=>'publish','menu-item-parent-id'=>$parent_item_id_mobile) );
                    }
                }
            }
        }

        $mas_item_id = wp_update_nav_menu_item( $menu_id, 0, array('menu-item-title'=>'Más','menu-item-url'=>'#','menu-item-status'=>'publish','menu-item-type'=>'custom') );

        foreach ( $secundarias as $parent ) {
            if ( $parent === 'Edictos y Carteles' ) {
                $carteles_page = get_page_by_path('edictos-y-carteles') ?: get_page_by_title('Carteles y Edictos');
                if ( $carteles_page ) {
                    wp_update_nav_menu_item( $menu_id, 0, array('menu-item-title'=>'Edictos y Carteles','menu-item-object-id'=>$carteles_page->ID,'menu-item-object'=>'page','menu-item-type'=>'post_type','menu-item-status'=>'publish','menu-item-parent-id'=>$mas_item_id) );
                    wp_update_nav_menu_item( $mobile_menu_id, 0, array('menu-item-title'=>'Edictos y Carteles','menu-item-object-id'=>$carteles_page->ID,'menu-item-object'=>'page','menu-item-type'=>'post_type','menu-item-status'=>'publish') );
                }
            } else {
                $page = get_page_by_title($parent) ?: get_page_by_path(sanitize_title($parent));
                if ( $page ) {
                    $parent_item_id_mas = wp_update_nav_menu_item( $menu_id, 0, array('menu-item-title'=>$parent,'menu-item-object-id'=>$page->ID,'menu-item-object'=>'page','menu-item-type'=>'post_type','menu-item-status'=>'publish','menu-item-parent-id'=>$mas_item_id) );
                    $parent_item_id_mob = wp_update_nav_menu_item( $mobile_menu_id, 0, array('menu-item-title'=>$parent,'menu-item-object-id'=>$page->ID,'menu-item-object'=>'page','menu-item-type'=>'post_type','menu-item-status'=>'publish') );
                    
                    foreach ( $estructura[$parent]['children'] as $child ) {
                        $child_page = get_page_by_title($child) ?: get_page_by_path(sanitize_title($child));
                        if ( $child_page ) {
                            wp_update_nav_menu_item( $menu_id, 0, array('menu-item-title'=>$child,'menu-item-object-id'=>$child_page->ID,'menu-item-object'=>'page','menu-item-type'=>'post_type','menu-item-status'=>'publish','menu-item-parent-id'=>$parent_item_id_mas) );
                            wp_update_nav_menu_item( $mobile_menu_id, 0, array('menu-item-title'=>$child,'menu-item-object-id'=>$child_page->ID,'menu-item-object'=>'page','menu-item-type'=>'post_type','menu-item-status'=>'publish','menu-item-parent-id'=>$parent_item_id_mob) );
                        }
                    }
                }
            }
        }

        $locations = get_theme_mod( 'nav_menu_locations' );
        if ( ! is_array( $locations ) ) { $locations = array(); }
        $locations['primary'] = $menu_id;
        $locations['mobile'] = $mobile_menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    }
}
add_action( 'init', 'pro_setup_espressivo_categories_and_menu' );

// Incluir módulo SSIVO-SEO
require_once get_template_directory() . '/inc/seo/init.php';

// Deshabilitar sitemap de usuarios por seguridad
add_filter(
    'wp_sitemaps_add_provider',
    function( $provider, $name ) {
        if ( 'users' === $name ) {
            return false;
        }
        return $provider;
    },
    10,
    2
);

// Módulo nativo: reportes editoriales PDF.
require_once get_template_directory() . '/inc/reportes/bootstrap.php';