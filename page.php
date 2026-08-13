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
 * La plantilla para mostrar páginas estáticas (como Contacto, Sobre Nosotros, etc.)
 *
 * @package pro
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container" style="max-width: 900px; padding: 40px 20px;">
        <?php
        while ( have_posts() ) :
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="page-header" style="margin-bottom: 30px; border-bottom: 2px solid var(--color-border); padding-bottom: 20px;">
                    <?php the_title( '<h1 class="entry-title" style="font-size: 2.5rem; color: var(--color-text); font-weight: bold; margin: 0;">', '</h1>' ); ?>
                </header><!-- .entry-header -->

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="page-thumbnail" style="margin-bottom: 30px;">
                        <?php the_post_thumbnail( 'full', array( 'style' => 'width: 100%; height: auto; border-radius: 8px;' ) ); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content" style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-muted); font-family: var(--font-ui);">
                    <?php
                    the_content();

                    wp_link_pages( array(
                        'before' => '<div class="page-links">' . esc_html__( 'Páginas:', 'pro' ),
                        'after'  => '</div>',
                    ) );
                    ?>
                </div><!-- .entry-content -->
            </article><!-- #post-<?php the_ID(); ?> -->
            <?php
        endwhile; // Fin del loop.
        ?>
    </div>
</main>

<?php
get_footer();
