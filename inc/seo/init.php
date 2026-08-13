<?php
namespace SSIVO_SEO;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once get_template_directory() . '/inc/seo/class-database.php';
require_once get_template_directory() . '/inc/seo/class-automations.php';
require_once get_template_directory() . '/inc/seo/class-frontend-meta.php';
require_once get_template_directory() . '/inc/seo/class-admin-page.php';
require_once get_template_directory() . '/inc/seo/class-metabox.php';

use SSIVO_SEO\Includes\Database;
use SSIVO_SEO\Includes\Automations;
use SSIVO_SEO\Includes\FrontendMeta;
use SSIVO_SEO\Includes\AdminPage;
use SSIVO_SEO\Includes\Metabox;

// 1. Crear tabla si no existe
add_action( 'admin_init', function() {
    if ( ! get_option( 'ssivo_seo_table_installed' ) ) {
        $db = new Database();
        $db->create_table();
        update_option( 'ssivo_seo_table_installed', true );
    }
});

// También crear la tabla al activar el tema si es posible
add_action( 'after_switch_theme', function() {
    $db = new Database();
    $db->create_table();
    update_option( 'ssivo_seo_table_installed', true );
});

// 2. Orquestar las clases
add_action( 'init', function() {
    $database = new Database();
    
    // Inicia el motor de automatización al publicar
    new Automations( $database );
    
    // Inicia la inyección de etiquetas en el frontend
    new FrontendMeta( $database );
    
    // Inicia la página de opciones y proxy REST
    new AdminPage();
    
    if ( is_admin() ) {
        new Metabox();
    }
});

// 3. Encolar los scripts de React en el editor
add_action( 'enqueue_block_editor_assets', function() {
    $asset_file = get_template_directory() . '/build/index.asset.php';
    if ( file_exists( $asset_file ) ) {
        $assets = require $asset_file;
        wp_enqueue_script(
            'ssivo-seo-editor',
            get_template_directory_uri() . '/build/index.js',
            $assets['dependencies'],
            $assets['version'],
            true
        );
    }
});
