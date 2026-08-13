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
 * La plantilla para mostrar páginas de error 404 (No encontrado)
 *
 * @package pro
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container" style="max-width: 800px; text-align: center; padding: 100px 20px;">
        <section class="error-404 not-found">
            <header class="page-header" style="margin-bottom: 40px;">
                <h1 class="page-title" style="font-size: clamp(6rem, 15vw, 10rem); font-weight: 900; color: var(--color-primary); margin: 0; line-height: 1; text-shadow: 4px 4px 0px rgba(0,0,0,0.05);">404</h1>
                <h2 style="font-size: 2rem; font-family: var(--font-ui); margin-top: 15px; color: var(--color-text);">¡Ups! Página no encontrada.</h2>
            </header>

            <div class="page-content" style="font-size: 1.15rem; color: var(--color-text-muted); line-height: 1.6; font-family: var(--font-ui);">
                <p><?php esc_html_e( 'Parece que no podemos encontrar lo que estás buscando. Puede que la dirección esté mal escrita o que la noticia haya sido archivada.', 'pro' ); ?></p>
                
                <div style="margin: 50px auto; max-width: 600px;">
                    <p style="margin-top: 0; font-weight: bold; color: var(--color-text); margin-bottom: 20px; font-size: 1.2rem;">Intenta buscar otra noticia:</p>
                    <?php get_search_form(); ?>
                </div>

                <div style="margin-top: 40px;">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display: inline-block; background-color: var(--color-primary); color: #fff; padding: 14px 35px; border-radius: 30px; text-decoration: none; font-weight: bold; font-family: var(--font-ui); transition: transform 0.3s ease, box-shadow 0.3s ease; box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 20px rgba(220, 38, 38, 0.4)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(220, 38, 38, 0.3)';">
                        Volver a la portada
                    </a>
                </div>
            </div>
        </section>
    </div>
</main>

<?php
get_footer();
