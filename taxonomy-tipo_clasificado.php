<?php
/**
 * Archivo público de tipos de clasificados (taxonomía).
 *
 * URL: /clasificados/tipo/categoria/
 *
 * @package Espressivo
 */

defined( 'ABSPATH' ) || exit;

// Utilizar la misma plantilla que el archivo principal de clasificados.
// La lógica interna de archive-clasificado.php ya detecta si es una taxonomía.
require get_template_directory() . '/archive-clasificado.php';
