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
 * Template Name: Página de Carteles y Edictos
 *
 * Plantilla de página para mostrar los carteles y edictos (CPT cartel).
 *
 * @package Pro
 */

get_header();
?>

<main id="primary" class="site-main container archive-container">

    <header class="page-header">
        <h1 class="page-title"><?php the_title(); ?></h1>
        <?php if ( get_the_content() ) : ?>
            <div class="page-description">
                <?php the_content(); ?>
            </div>
        <?php endif; ?>
    </header><!-- .page-header -->

    <?php 
    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
    $carteles_query = new WP_Query( array(
        'post_type'      => 'cartel',
        'posts_per_page' => 12,
        'paged'          => $paged
    ) );
    
    if ( $carteles_query->have_posts() ) : ?>
        <div class="carteles-grid">
            <?php
            while ( $carteles_query->have_posts() ) :
                $carteles_query->the_post();
                
                // Obtener el PDF adjunto
                $pdf_url = get_post_meta( get_the_ID(), '_cartel_pdf_url', true );
                if ( $pdf_url && strpos( $pdf_url, 'http' ) !== 0 ) {
                    global $wpdb;
                    $attach_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s LIMIT 1", '%' . $wpdb->esc_like( $pdf_url ) ) );
                    if ( $attach_id ) {
                        $pdf_url = wp_get_attachment_url( $attach_id );
                    } else {
                        $upload_dir = wp_upload_dir();
                        $pdf_url = $upload_dir['baseurl'] . '/' . $pdf_url;
                    }
                }
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('card-cartel'); ?> data-pdf-url="<?php echo esc_url($pdf_url); ?>">
                    <div class="cartel-thumbnail">
                        <?php 
                        if ( has_post_thumbnail() ) {
                            the_post_thumbnail( 'medium', array( 'loading' => 'lazy' ) );
                        } else if ( ! empty( $pdf_url ) ) {
                            echo '<div class="pdf-preview-container" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; background: #fff;">';
                            echo '<iframe src="' . esc_url($pdf_url) . '#toolbar=0&navpanes=0&scrollbar=0&view=FitH" frameborder="0" style="width: 100%; height: 100%; pointer-events: none; border: none;" scrolling="no"></iframe>';
                            echo '</div>';
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
        // Paginación personalizada para WP_Query
        $total_pages = $carteles_query->max_num_pages;
        if ($total_pages > 1) {
            $current_page = max(1, get_query_var('paged'));
            echo '<nav class="navigation pagination" aria-label="Entradas"><div class="nav-links">';
            echo paginate_links(array(
                'base' => get_pagenum_link(1) . '%_%',
                'format' => 'page/%#%/',
                'current' => $current_page,
                'total' => $total_pages,
                'prev_text' => '&larr; Anteriores',
                'next_text' => 'Siguientes &rarr;',
            ));
            echo '</div></nav>';
        }
        
        wp_reset_postdata();
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
