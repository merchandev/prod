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
 * Banner de acceso rápido a Carteles y Edictos para la portada.
 *
 * @package Pro
 */
?>

<section class="home-carteles-banner">
    <div class="carteles-banner-content">
        <div class="banner-icon">
            <span class="material-symbols-outlined" aria-hidden="true">gavel</span>
        </div>
        <div class="banner-text">
            <h2 class="banner-title">Carteles y Edictos</h2>
            <p class="banner-desc">Consulta los últimos avisos legales, notificaciones y resoluciones oficiales de la región.</p>
        </div>
        <div class="banner-action">
            <a href="<?php echo esc_url( home_url( '/carteles/' ) ); ?>" class="btn-carteles">Ver Documentos <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span></a>
        </div>
    </div>
</section>
