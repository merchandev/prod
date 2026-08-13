<?php
/**
 * Módulo nativo de reportes editoriales PDF.
 *
 * Se carga desde functions.php y forma parte del tema Espressivo.
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'ESPRESSIVO_REPORTES_VERSION' ) ) {
    define( 'ESPRESSIVO_REPORTES_VERSION', '1.0.0' );
}

if ( ! defined( 'ESPRESSIVO_REPORTES_DIR' ) ) {
    define( 'ESPRESSIVO_REPORTES_DIR', trailingslashit( get_template_directory() ) . 'inc/reportes/' );
}

if ( ! defined( 'ESPRESSIVO_REPORTES_URL' ) ) {
    define( 'ESPRESSIVO_REPORTES_URL', trailingslashit( get_template_directory_uri() ) . 'inc/reportes/' );
}

// Dompdf queda integrado en el ZIP de distribución del tema mediante Composer.
$espressivo_composer_autoload = trailingslashit( get_template_directory() ) . 'vendor/autoload.php';
if ( is_readable( $espressivo_composer_autoload ) ) {
    require_once $espressivo_composer_autoload;
}

require_once ESPRESSIVO_REPORTES_DIR . 'class-espressivo-upload-log.php';
require_once ESPRESSIVO_REPORTES_DIR . 'class-espressivo-report-service.php';
require_once ESPRESSIVO_REPORTES_DIR . 'class-espressivo-pdf-generator.php';
require_once ESPRESSIVO_REPORTES_DIR . 'class-espressivo-reportes.php';

add_action(
    'after_switch_theme',
    array(
        'Espressivo_Reportes',
        'activate',
    )
);

Espressivo_Reportes::boot();
