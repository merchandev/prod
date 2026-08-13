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
 * Plantilla para el formulario de búsqueda global
 */
?>
<form role="search" method="get" class="search-form custom-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Buscar noticias...', 'placeholder', 'pro' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" autocomplete="off" aria-label="<?php echo esc_attr_x( 'Buscar por:', 'label', 'pro' ); ?>" />
    <button type="submit" class="search-submit" aria-label="Buscar">
        <span class="material-symbols-outlined" aria-hidden="true">search</span>
    </button>
</form>
