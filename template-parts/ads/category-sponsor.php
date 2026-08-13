<?php
/**
 * Category Sponsor Banner Template Part
 *
 * Muestra un banner de patrocinador en la parte superior de la página o categoría
 * con soporte para slides múltiples de imágenes.
 *
 * Argumentos opcionales:
 *   'cat_name'   (string) – Nombre de la categoría/página a mostrar
 *
 * @package Pro
 */

// Intentar obtener el ID y tipo actual
$obj_id = get_queried_object_id();
$obj_type = is_page() ? 'page' : (is_category() ? 'category' : '');

// Obtener argumentos
$cat_name = isset( $args['cat_name'] ) ? $args['cat_name'] : (is_category() ? single_cat_title('', false) : get_the_title());

// Intentar obtener el banner para esta categoría o página
$banner_data = function_exists( 'pro_get_category_banner' ) ? pro_get_category_banner( $obj_id, $obj_type ) : null;

// Si no hay anuncio activo, verificamos si mostramos el placeholder
if ( empty( $banner_data ) ) {
    $show_placeholders = get_theme_mod( 'pro_show_ad_placeholders', true );
    if ( ! $show_placeholders ) {
        return;
    }
}

// Extraer datos del patrocinador
$sponsor_name = !empty($banner_data['sponsor_name']) ? $banner_data['sponsor_name'] : (!empty($banner_data['title']) ? $banner_data['title'] : '');
$sponsor_logo = !empty($banner_data['sponsor_logo']) ? $banner_data['sponsor_logo'] : '';
$ad_url       = (!empty($banner_data['url']) && $banner_data['url'] !== '#') ? esc_url($banner_data['url']) : '#';
$slides       = !empty($banner_data['slides']) ? $banner_data['slides'] : array();
?>

<!-- BANNER PATROCINADOR -->
<div class="category-sponsor-banner" style="margin-bottom: 30px;">

    <!-- Cabecera: Nombre de sección + "Patrocinado por" -->
    <div class="sponsor-header">
        <?php if ( ! empty( $cat_name ) ) : ?>
            <span class="sponsor-section-name"><?php echo esc_html( strtoupper( $cat_name ) ); ?></span>
            <span class="sponsor-divider"></span>
        <?php endif; ?>
        <span class="sponsor-label">
            <span class="sponsor-label-text">Patrocinado por</span>
            <?php if ( ! empty( $sponsor_name ) || ! empty( $sponsor_logo ) ) : ?>
                <?php if ( $ad_url !== '#' ) : ?>
                    <a href="<?php echo $ad_url; ?>" target="_blank" rel="noopener noreferrer sponsored" class="sponsor-name-link">
                        <?php if ( ! empty( $sponsor_logo ) ) : ?>
                            <img src="<?php echo esc_url( $sponsor_logo ); ?>" alt="<?php echo esc_attr( $sponsor_name ); ?>" class="sponsor-logo" style="max-height: 30px; vertical-align: middle;">
                        <?php else : ?>
                            <strong class="sponsor-name"><?php echo esc_html( $sponsor_name ); ?></strong>
                        <?php endif; ?>
                    </a>
                <?php else : ?>
                    <?php if ( ! empty( $sponsor_logo ) ) : ?>
                        <img src="<?php echo esc_url( $sponsor_logo ); ?>" alt="<?php echo esc_attr( $sponsor_name ); ?>" class="sponsor-logo" style="max-height: 30px; vertical-align: middle;">
                    <?php else : ?>
                        <strong class="sponsor-name"><?php echo esc_html( $sponsor_name ); ?></strong>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </span>
    </div>

    <!-- Banner publicitario (Slides) -->
    <div class="sponsor-banner-content" style="position: relative; overflow: hidden; border-radius: 8px;">
        <?php if ( ! empty( $slides ) ) : ?>
            <div class="pro-sponsor-slider" id="pro-sponsor-slider-<?php echo $obj_id; ?>" style="display: flex; transition: transform 0.5s ease-in-out; width: 100%;">
                <?php foreach ( $slides as $index => $img_url ) : ?>
                    <div class="pro-sponsor-slide" style="min-width: 100%; box-sizing: border-box;">
                        <?php if ( $ad_url !== '#' ) : ?>
                            <a href="<?php echo $ad_url; ?>" target="_blank" rel="noopener noreferrer sponsored" class="sponsor-banner-link" style="display: block;">
                                <img src="<?php echo esc_url( $img_url ); ?>" alt="Patrocinador" class="sponsor-banner-img" style="width: 100%; height: auto; display: block;">
                            </a>
                        <?php else : ?>
                            <img src="<?php echo esc_url( $img_url ); ?>" alt="Patrocinador" class="sponsor-banner-img" style="width: 100%; height: auto; display: block;">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ( count($slides) > 1 ) : ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const slider = document.getElementById('pro-sponsor-slider-<?php echo $obj_id; ?>');
                    const slides = slider.querySelectorAll('.pro-sponsor-slide');
                    const totalSlides = slides.length;
                    let currentIndex = 0;
                    
                    setInterval(function() {
                        currentIndex = (currentIndex + 1) % totalSlides;
                        slider.style.transform = `translateX(-${currentIndex * 100}%)`;
                    }, 4000); // Rota cada 4 segundos
                });
            </script>
            <?php endif; ?>
            
        <?php else : ?>
            <!-- Placeholder cuando no hay anuncio publicado -->
            <?php if ( get_theme_mod( 'pro_show_ad_placeholders', true ) ) : ?>
                <div class="sponsor-banner-placeholder" style="background: #111; color: #fff; text-align: center; padding: 30px; border-radius: 8px;">
                    <span class="sponsor-placeholder-icon material-symbols-outlined" aria-hidden="true" style="font-size: 30px; display: block; margin-bottom: 10px;">campaign</span>
                    <span style="font-size: 14px; text-transform: uppercase; letter-spacing: 1px; display: block;">
                        <?php echo ! empty( $cat_name ) ? esc_html( $cat_name ) . ' — ' : ''; ?>Espacio Publicitario
                    </span>
                    <span class="sponsor-placeholder-size" style="font-size: 12px; color: #888; display: block; margin-top: 5px;">728 × 90 px</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</div><!-- .category-sponsor-banner -->
