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
 * In-Feed Ad Template Part
 * 
 * Used for displaying rotating ads in feed zones.
 * Requires an argument 'location' (e.g. 'in-feed-1') passed via get_template_part.
 */

// If no arguments were provided, default to in-feed-1
$location = isset($args['location']) ? $args['location'] : 'in-feed-1';
$ads = function_exists('pro_get_active_ads') ? pro_get_active_ads($location) : array();

// Si no hay anuncios publicados, no mostrar nada (evita los bloques vacíos que rompen el layout)
if ( empty($ads) ) {
    return;
}
?>

<!-- PUBLICIDAD: <?php echo esc_html(strtoupper($location)); ?> -->
<div class="in-feed-ad-wrapper">
    <div class="ad-label-tag">Publicidad</div>
    
    <div class="in-feed-ad-slider">
        <?php foreach ($ads as $index => $ad) : 
            $active_class = ($index === 0) ? ' active' : '';
            $url = !empty($ad['url']) ? esc_url($ad['url']) : '#';
        ?>
        <div class="ad-slide<?php echo $active_class; ?>">
            <a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer" class="in-feed-ad-link">
                <img src="<?php echo esc_url($ad['image']); ?>" alt="<?php echo esc_attr($ad['title']); ?>" class="in-feed-ad-img">
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
