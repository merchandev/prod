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
 * Plantilla de Archivo para Carteles y Edictos
 *
 * @package Pro
 */

get_header();
?>

<main id="primary" class="site-main container archive-container">

    <header class="page-header">
        <h1 class="page-title">Carteles y Edictos</h1>
    </header><!-- .page-header -->

    <?php if ( have_posts() ) : ?>
        <div class="carteles-grid">
            <?php
            while ( have_posts() ) :
                the_post();
                
                // Obtener el PDF adjunto
                $pdf_url = get_post_meta( get_the_ID(), '_cartel_pdf_url', true );
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('card-cartel'); ?> data-pdf-url="<?php echo esc_url($pdf_url); ?>">
                    <div class="cartel-thumbnail">
                        <?php 
                        if ( has_post_thumbnail() ) {
                            the_post_thumbnail( 'medium', array( 'loading' => 'lazy' ) );
                        } else {
                            echo '<div class="placeholder-cartel"><span class="material-symbols-outlined" aria-hidden="true">description</span></div>';
                        }
                        ?>
                        <div class="cartel-overlay">
                            <span class="material-symbols-outlined cartel-icon" aria-hidden="true">visibility</span>
                            <span>Ver Documento</span>
                        </div>
                    </div>
                    <div class="cartel-content">
                        <div class="post-meta">
                            <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                        </div>
                        <h2 class="entry-title"><?php the_title(); ?></h2>
                    </div>
                </article>
                <?php
            endwhile;
            ?>
        </div>

        <?php
        the_posts_pagination( array(
            'prev_text' => '&larr; Anteriores',
            'next_text' => 'Siguientes &rarr;',
        ) );
        ?>

    <?php else : ?>
        <section class="no-results not-found">
            <div class="page-content">
                <p><?php esc_html_e( 'Actualmente no hay carteles ni edictos publicados.', 'pro' ); ?></p>
            </div>
        </section>
    <?php endif; ?>

</main><!-- #primary -->

<!-- Modal Visor PDF -->
<div id="pdf-lightbox-modal" class="pdf-modal">
    <div class="pdf-modal-content">
        <div class="pdf-modal-header">
            <h3 id="pdf-modal-title">Visor de Documento</h3>
            <button class="pdf-modal-close" aria-label="Cerrar visor">&times;</button>
        </div>
        <div class="pdf-modal-body">
            <iframe id="pdf-iframe" src="" frameborder="0" width="100%" height="100%"></iframe>
        </div>
    </div>
</div>

<?php
get_footer();
