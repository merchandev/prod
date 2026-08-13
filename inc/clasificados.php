<?php
/**
 * Sistema editorial de clasificados.
 *
 * @package Espressivo
 */

defined( 'ABSPATH' ) || exit;

const ESPRESSIVO_CLASIFICADOS_VERSION = '1.0.0';

/**
 * Registrar el CPT y su única taxonomía.
 */
function espressivo_register_clasificados(): void {
    $labels = array(
        'name'                  => __( 'Clasificados', 'pro' ),
        'singular_name'         => __( 'Clasificado', 'pro' ),
        'menu_name'             => __( 'Clasificados', 'pro' ),
        'name_admin_bar'        => __( 'Clasificado', 'pro' ),

        'add_new'               => __( 'Añadir clasificado', 'pro' ),
        'add_new_item'          => __( 'Añadir nuevo clasificado', 'pro' ),
        'new_item'              => __( 'Nuevo clasificado', 'pro' ),
        'edit_item'             => __( 'Editar clasificado', 'pro' ),
        'view_item'             => __( 'Ver clasificado', 'pro' ),

        'all_items'             => __( 'Todos los clasificados', 'pro' ),
        'search_items'          => __( 'Buscar clasificados', 'pro' ),
        'not_found'             => __( 'No se encontraron clasificados.', 'pro' ),
        'not_found_in_trash'    => __( 'No hay clasificados en la papelera.', 'pro' ),

        'archives'              => __( 'Archivo de clasificados', 'pro' ),
        'attributes'            => __( 'Atributos del clasificado', 'pro' ),
        'featured_image'        => __( 'Imagen', 'pro' ),
        'set_featured_image'    => __( 'Asignar imagen', 'pro' ),
        'remove_featured_image' => __( 'Eliminar imagen', 'pro' ),

        'item_published'        => __( 'Clasificado publicado.', 'pro' ),
        'item_updated'          => __( 'Clasificado actualizado.', 'pro' ),
    );

    register_post_type(
        'clasificado',
        array(
            'labels' => $labels,

            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_admin_bar'  => true,
            'show_in_nav_menus'  => true,
            'show_in_rest'       => true,

            'menu_position' => 25,
            'menu_icon'     => 'dashicons-megaphone',

            /*
             * Archivo:
             * /clasificados/
             *
             * Individual:
             * /clasificados/titulo-del-aviso/
             */
            'has_archive' => 'clasificados',

            'rewrite' => array(
                'slug'       => 'clasificados',
                'with_front' => false,
                'feeds'      => true,
                'pages'      => true,
            ),

            'query_var' => true,

            /*
             * Solo texto.
             * No se habilita thumbnail.
             */
            'supports' => array(
                'title',
                'editor',
                'author',
                'revisions',
            ),

            'taxonomies' => array(
                'tipo_clasificado',
            ),

            'capability_type' => 'clasificado',
            'map_meta_cap'    => true,

            'can_export'         => true,
            'delete_with_user'   => false,
            'exclude_from_search'=> false,
        )
    );

    register_taxonomy(
        'tipo_clasificado',
        array( 'clasificado' ),
        array(
            'labels' => array(
                'name'                       => __( 'Tipos de clasificado', 'pro' ),
                'singular_name'              => __( 'Tipo de clasificado', 'pro' ),
                'menu_name'                  => __( 'Tipos de clasificado', 'pro' ),
                'search_items'               => __( 'Buscar tipos', 'pro' ),
                'all_items'                  => __( 'Todos los tipos', 'pro' ),
                'parent_item'                => __( 'Tipo superior', 'pro' ),
                'parent_item_colon'          => __( 'Tipo superior:', 'pro' ),
                'edit_item'                  => __( 'Editar tipo', 'pro' ),
                'update_item'                => __( 'Actualizar tipo', 'pro' ),
                'add_new_item'               => __( 'Añadir tipo', 'pro' ),
                'new_item_name'              => __( 'Nombre del nuevo tipo', 'pro' ),
                'not_found'                  => __( 'No se encontraron tipos.', 'pro' ),
                'back_to_items'              => __( 'Volver a tipos', 'pro' ),
                'separate_items_with_commas' => __( 'Separar tipos con comas', 'pro' ),
            ),

            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_admin_column'  => true,
            'show_in_nav_menus'  => true,
            'show_in_rest'       => true,

            'capabilities'       => array(
                'manage_terms' => 'manage_tipos_clasificados',
                'edit_terms'   => 'edit_tipos_clasificados',
                'delete_terms' => 'delete_tipos_clasificados',
                'assign_terms' => 'assign_tipos_clasificados',
            ),

            /*
             * Funciona como categorías:
             * admite tipos y subtipos.
             */
            'hierarchical' => true,

            /*
             * Deshabilitamos el rewrite nativo para controlarlo
             * manualmente con custom rewrite rules y obtener
             * URLs del tipo /clasificados-mascotas/
             */
            'rewrite'      => false,

            'query_var' => true,
        )
    );
}
add_action( 'init', 'espressivo_register_clasificados', 8 );

/**
 * Reglas de reescritura personalizadas para la taxonomía.
 * Permite URLs como /clasificados-categoria/
 */
function espressivo_tipo_clasificado_rewrite_rules() {
    // Regla para paginación
    add_rewrite_rule(
        '^(clasificados-[^/]*)/page/([0-9]{1,})/?$',
        'index.php?tipo_clasificado=$matches[1]&paged=$matches[2]',
        'top'
    );
    // Regla para la página principal de la categoría
    add_rewrite_rule(
        '^(clasificados-[^/]*)/?$',
        'index.php?tipo_clasificado=$matches[1]',
        'top'
    );
}
add_action( 'init', 'espressivo_tipo_clasificado_rewrite_rules' );

/**
 * Modificar la URL que devuelve get_term_link() para que coincida con nuestras reglas.
 */
function espressivo_tipo_clasificado_term_link( $url, $term, $taxonomy ) {
    if ( 'tipo_clasificado' === $taxonomy ) {
        return home_url( user_trailingslashit( $term->slug ) );
    }
    return $url;
}
add_filter( 'term_link', 'espressivo_tipo_clasificado_term_link', 10, 3 );

/**
 * Migrar slugs existentes y limpiar las reglas de reescritura 
 * (se ejecuta una sola vez).
 */
function espressivo_migrate_clasificado_slugs() {
    if ( get_option( 'espressivo_clasificados_slugs_migrated_v2' ) ) {
        return;
    }
    
    $terms = get_terms( array(
        'taxonomy'   => 'tipo_clasificado',
        'hide_empty' => false,
    ) );
    
    if ( ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            // Si el slug no empieza por 'clasificados-'
            if ( strpos( $term->slug, 'clasificados-' ) !== 0 ) {
                $new_slug = 'clasificados-' . $term->slug;
                wp_update_term( $term->term_id, 'tipo_clasificado', array(
                    'slug' => $new_slug
                ) );
            }
        }
    }
    
    // Forzar limpieza de las reglas de reescritura para arreglar errores 404
    flush_rewrite_rules( false );
    update_option( 'espressivo_clasificados_slugs_migrated_v2', true );
}
add_action( 'init', 'espressivo_migrate_clasificado_slugs', 99 );

/**
 * Limitar el editor a bloques de texto.
 *
 * Evita imágenes, videos, galerías y archivos.
 */
function espressivo_clasificados_allowed_blocks(
    $allowed_blocks,
    $editor_context
) {
    if (
        empty( $editor_context->post )
        || 'clasificado' !== $editor_context->post->post_type
    ) {
        return $allowed_blocks;
    }

    return array(
        'core/paragraph',
        'core/heading',
        'core/list',
        'core/list-item',
        'core/quote',
        'core/separator',
    );
}
add_filter(
    'allowed_block_types_all',
    'espressivo_clasificados_allowed_blocks',
    10,
    2
);

/**
 * Cambiar el placeholder del título.
 */
function espressivo_clasificado_title_placeholder(
    string $placeholder,
    WP_Post $post
): string {
    if ( 'clasificado' === $post->post_type ) {
        return __(
            'Escribe el título del clasificado',
            'pro'
        );
    }

    return $placeholder;
}
add_filter(
    'enter_title_here',
    'espressivo_clasificado_title_placeholder',
    10,
    2
);

/**
 * Comprobar que un clasificado tenga:
 *
 * - Título.
 * - Texto.
 * - Tipo de clasificado.
 *
 * Si falta algo, se guarda como borrador.
 */
function espressivo_validate_clasificado(
    int $post_id,
    WP_Post $post,
    bool $update,
    ?WP_Post $post_before
): void {
    static $validating = false;

    if (
        $validating
        || 'clasificado' !== $post->post_type
        || 'publish' !== $post->post_status
        || wp_is_post_revision( $post_id )
        || wp_is_post_autosave( $post_id )
    ) {
        return;
    }

    $errors = array();

    $title = trim(
        wp_strip_all_tags( $post->post_title )
    );

    $content = trim(
        wp_strip_all_tags(
            strip_shortcodes( $post->post_content )
        )
    );

    if ( '' === $title ) {
        $errors[] = __(
            'Debes escribir un título.',
            'pro'
        );
    }

    if ( '' === $content ) {
        $errors[] = __(
            'Debes escribir el texto del clasificado.',
            'pro'
        );
    }

    $type_ids = wp_get_post_terms(
        $post_id,
        'tipo_clasificado',
        array(
            'fields' => 'ids',
        )
    );

    if (
        is_wp_error( $type_ids )
        || empty( $type_ids )
    ) {
        $errors[] = __(
            'Debes seleccionar un tipo de clasificado.',
            'pro'
        );
    }

    if ( empty( $errors ) ) {
        return;
    }

    $validating = true;

    wp_update_post(
        array(
            'ID'          => $post_id,
            'post_status' => 'draft',
        )
    );

    $validating = false;

    set_transient(
        'espressivo_clasificado_errors_'
            . get_current_user_id(),
        $errors,
        MINUTE_IN_SECONDS
    );
}
add_action(
    'wp_after_insert_post',
    'espressivo_validate_clasificado',
    100,
    4
);

/**
 * Mostrar las validaciones.
 */
function espressivo_clasificado_admin_notice(): void {
    $transient_key =
        'espressivo_clasificado_errors_'
        . get_current_user_id();

    $errors = get_transient( $transient_key );

    if ( empty( $errors ) || ! is_array( $errors ) ) {
        return;
    }

    delete_transient( $transient_key );
    ?>
    <div class="notice notice-error is-dismissible">
        <p>
            <strong>
                <?php
                esc_html_e(
                    'El clasificado se guardó como borrador:',
                    'pro'
                );
                ?>
            </strong>
        </p>

        <ul>
            <?php foreach ( $errors as $error ) : ?>
                <li>
                    <?php echo esc_html( $error ); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}
add_action(
    'admin_notices',
    'espressivo_clasificado_admin_notice'
);

/**
 * Configurar la consulta pública.
 */
function espressivo_clasificados_main_query(
    WP_Query $query
): void {
    if (
        is_admin()
        || ! $query->is_main_query()
    ) {
        return;
    }

    if (
        ! $query->is_post_type_archive( 'clasificado' )
        && ! $query->is_tax( 'tipo_clasificado' )
    ) {
        return;
    }

    $query->set( 'post_status', 'publish' );
    $query->set( 'posts_per_page', 36 );
    $query->set(
        'orderby',
        array(
            'date' => 'DESC',
            'ID'   => 'DESC',
        )
    );

    /*
     * Búsqueda dentro de /clasificados/.
     */
    if ( isset( $_GET['buscar_clasificado'] ) ) {
        $search = sanitize_text_field(
            wp_unslash(
                $_GET['buscar_clasificado']
            )
        );

        if ( '' !== $search ) {
            $query->set( 's', $search );
        }
    }

    /*
     * Filtrar por tipo sin abandonar /clasificados/.
     */
    if (
        $query->is_post_type_archive( 'clasificado' )
        && isset( $_GET['tipo'] )
    ) {
        $type_slug = sanitize_title(
            wp_unslash( $_GET['tipo'] )
        );

        if ( '' !== $type_slug ) {
            $query->set(
                'tax_query',
                array(
                    array(
                        'taxonomy' => 'tipo_clasificado',
                        'field'    => 'slug',
                        'terms'    => array( $type_slug ),
                    ),
                )
            );
        }
    }
}
add_action(
    'pre_get_posts',
    'espressivo_clasificados_main_query'
);

/**
 * Columnas administrativas.
 */
function espressivo_clasificado_columns(
    array $columns
): array {
    $new_columns = array();

    foreach ( $columns as $key => $label ) {
        $new_columns[ $key ] = $label;

        if ( 'title' === $key ) {
            $new_columns['clasificado_type'] =
                __( 'Tipo', 'pro' );
        }
    }

    return $new_columns;
}
add_filter(
    'manage_clasificado_posts_columns',
    'espressivo_clasificado_columns'
);

function espressivo_clasificado_column_content(
    string $column,
    int $post_id
): void {
    if ( 'clasificado_type' !== $column ) {
        return;
    }

    $terms = get_the_terms(
        $post_id,
        'tipo_clasificado'
    );

    if (
        empty( $terms )
        || is_wp_error( $terms )
    ) {
        echo '<span aria-hidden="true">—</span>';
        return;
    }

    echo esc_html(
        implode(
            ', ',
            wp_list_pluck( $terms, 'name' )
        )
    );
}
add_action(
    'manage_clasificado_posts_custom_column',
    'espressivo_clasificado_column_content',
    10,
    2
);

/**
 * Crear tipos iniciales.
 */
function espressivo_seed_clasificado_types(): void {
    $types = array(
        'Empleos' => 'work',
        'Inmuebles' => 'real_estate_agent',
        'Vehículos' => 'directions_car',
        'Compra y venta' => 'storefront',
        'Servicios' => 'home_repair_service',
        'Mascotas' => 'pets',
        'Educación' => 'school',
        'Avisos profesionales' => 'badge',
        'Otros' => 'more_horiz',
    );

    foreach ( $types as $type_name => $icon ) {
        $term = term_exists( $type_name, 'tipo_clasificado' );
        
        if ( ! $term ) {
            $slug = 'clasificados-' . sanitize_title( $type_name );
            $term = wp_insert_term( $type_name, 'tipo_clasificado', array( 'slug' => $slug ) );
        }
        
        if ( ! is_wp_error( $term ) && is_array( $term ) ) {
            $term_id = $term['term_id'];
            if ( ! metadata_exists( 'term', $term_id, 'clasificado_icon' ) ) {
                update_term_meta( $term_id, 'clasificado_icon', $icon );
            }
        }
    }
}

/**
 * Añadir campo de icono al formulario de nueva categoría.
 */
function espressivo_tipo_clasificado_add_form_fields(): void {
    ?>
    <div class="form-field">
        <label for="clasificado_icon"><?php esc_html_e( 'Icono (Clase o URL)', 'pro' ); ?></label>
        <input name="clasificado_icon" id="clasificado_icon" type="text" value="" size="40">
        <p class="description"><?php esc_html_e( 'Ingresa el nombre del icono de Google Fonts (ej. directions_car) o la URL completa de una imagen (SVG/PNG/WEBP).', 'pro' ); ?></p>
    </div>
    <?php
}
add_action( 'tipo_clasificado_add_form_fields', 'espressivo_tipo_clasificado_add_form_fields' );

/**
 * Añadir campo de icono al formulario de edición de categoría.
 */
function espressivo_tipo_clasificado_edit_form_fields( WP_Term $term ): void {
    $icon = get_term_meta( $term->term_id, 'clasificado_icon', true );
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="clasificado_icon"><?php esc_html_e( 'Icono (Clase o URL)', 'pro' ); ?></label></th>
        <td>
            <input name="clasificado_icon" id="clasificado_icon" type="text" value="<?php echo esc_attr( $icon ); ?>" size="40">
            <p class="description"><?php esc_html_e( 'Ingresa el nombre del icono de Google Fonts (ej. directions_car) o la URL completa de una imagen (SVG/PNG/WEBP).', 'pro' ); ?></p>
        </td>
    </tr>
    <?php
}
add_action(
    'tipo_clasificado_edit_form_fields',
    'espressivo_tipo_clasificado_edit_form_fields'
);

/**
 * Guardar el campo de icono de la categoría.
 */
function espressivo_save_tipo_clasificado_meta( int $term_id ): void {
    if ( isset( $_POST['clasificado_icon'] ) ) {
        $icon = sanitize_text_field( wp_unslash( $_POST['clasificado_icon'] ) );
        update_term_meta( $term_id, 'clasificado_icon', $icon );
    }
}
add_action( 'created_tipo_clasificado', 'espressivo_save_tipo_clasificado_meta' );
add_action( 'edited_tipo_clasificado', 'espressivo_save_tipo_clasificado_meta' );

/**
 * Asignar permisos (capabilities) de Clasificados a los roles requeridos.
 * (Solo se ejecuta una vez por optimización).
 */
function espressivo_grant_clasificado_capabilities() {
    if ( get_option( 'espressivo_clasificado_caps_granted_v1' ) ) {
        return;
    }

    // Roles que tendrán acceso a los clasificados
    $roles_con_acceso = array( 
        'administrator', 
        'publicista', 
        'author', 
        'editor', 
        'gerencia', 
        'direccion' 
    );

    $caps_cpt = array(
        'edit_clasificado',
        'read_clasificado',
        'delete_clasificado',
        'edit_clasificados',
        'edit_others_clasificados',
        'publish_clasificados',
        'read_private_clasificados',
        'delete_clasificados',
        'delete_private_clasificados',
        'delete_published_clasificados',
        'delete_others_clasificados',
        'edit_private_clasificados',
        'edit_published_clasificados',
    );

    $caps_tax = array(
        'manage_tipos_clasificados',
        'edit_tipos_clasificados',
        'delete_tipos_clasificados',
        'assign_tipos_clasificados',
    );

    foreach ( $roles_con_acceso as $role_name ) {
        $role = get_role( $role_name );
        if ( ! $role ) continue;

        // Añadir capacidades del post type
        foreach ( $caps_cpt as $cap ) {
            // El 'author' nativo de WP solo debe poder gestionar sus propios clasificados
            if ( 'author' === $role_name && in_array( $cap, array(
                'edit_others_clasificados',
                'read_private_clasificados',
                'delete_others_clasificados',
                'delete_private_clasificados',
                'edit_private_clasificados'
            ), true ) ) {
                continue;
            }

            $role->add_cap( $cap );
        }

        // Añadir capacidades de la taxonomía (categorías)
        foreach ( $caps_tax as $cap_tax ) {
            // Un 'author' comúnmente no puede administrar/borrar categorías globales, solo asignarlas
            if ( 'author' === $role_name && 'assign_tipos_clasificados' !== $cap_tax ) {
                continue;
            }
            $role->add_cap( $cap_tax );
        }
    }

    update_option( 'espressivo_clasificado_caps_granted_v1', true );
}
add_action( 'init', 'espressivo_grant_clasificado_capabilities', 99 );

/**
 * Migración versionada.
 *
 * No ejecutar flush_rewrite_rules() en cada visita.
 */
function espressivo_upgrade_clasificados(): void {
    $installed_version = get_option(
        'espressivo_clasificados_version',
        '0'
    );

    if (
        version_compare(
            $installed_version,
            ESPRESSIVO_CLASIFICADOS_VERSION,
            '>='
        )
    ) {
        return;
    }

    espressivo_seed_clasificado_types();

    /*
     * Soft flush: solo se ejecuta al cambiar versión.
     */
    flush_rewrite_rules( false );

    update_option(
        'espressivo_clasificados_version',
        ESPRESSIVO_CLASIFICADOS_VERSION,
        false
    );
}
add_action(
    'admin_init',
    'espressivo_upgrade_clasificados'
);

/**
 * Al activar el tema.
 */
function espressivo_activate_clasificados(): void {
    espressivo_register_clasificados();
    espressivo_seed_clasificado_types();

    flush_rewrite_rules( false );

    update_option(
        'espressivo_clasificados_version',
        ESPRESSIVO_CLASIFICADOS_VERSION,
        false
    );
}
add_action(
    'after_switch_theme',
    'espressivo_activate_clasificados'
);

/**
 * Cargar estilos únicamente en clasificados.
 */
function espressivo_enqueue_clasificados_assets(): void {
    if (
        ! is_post_type_archive( 'clasificado' )
        && ! is_singular( 'clasificado' )
        && ! is_tax( 'tipo_clasificado' )
    ) {
        return;
    }

    $css_path =
        get_template_directory()
        . '/assets/css/clasificados.css';

    $version = is_readable( $css_path )
        ? (string) filemtime( $css_path )
        : ESPRESSIVO_CLASIFICADOS_VERSION;

    wp_enqueue_style(
        'espressivo-clasificados',
        get_template_directory_uri()
            . '/assets/css/clasificados.css',
        array( 'pro-main-style' ),
        $version
    );
}
add_action(
    'wp_enqueue_scripts',
    'espressivo_enqueue_clasificados_assets',
    20
);

/**
 * Migración de clasificados actuales (Si no tienen tipo, pasan a 'Otros')
 */
function espressivo_classify_legacy_classifieds(): void {
    if (
        get_option(
            'espressivo_legacy_classifieds_migrated'
        )
    ) {
        return;
    }

    $other_term = term_exists(
        'Otros',
        'tipo_clasificado'
    );

    if ( ! $other_term ) {
        $other_term = wp_insert_term(
            'Otros',
            'tipo_clasificado'
        );
    }

    if ( is_wp_error( $other_term ) ) {
        return;
    }

    $other_term_id = is_array( $other_term )
        ? (int) $other_term['term_id']
        : (int) $other_term;

    $classified_ids = get_posts(
        array(
            'post_type'      => 'clasificado',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',

            'tax_query' => array(
                array(
                    'taxonomy' => 'tipo_clasificado',
                    'operator' => 'NOT EXISTS',
                ),
            ),
        )
    );

    foreach ( $classified_ids as $classified_id ) {
        wp_set_object_terms(
            $classified_id,
            array( $other_term_id ),
            'tipo_clasificado',
            false
        );
    }

    update_option(
        'espressivo_legacy_classifieds_migrated',
        true,
        false
    );
}
add_action(
    'admin_init',
    'espressivo_classify_legacy_classifieds',
    30
);
