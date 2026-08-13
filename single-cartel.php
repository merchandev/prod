<?php
/**
 * @author Arturo Merchan | Merchan.Dev | Espressivo Venezuela,C.A
 * 
 * Plantilla para carteles individuales (CPT cartel).
 * Muestra el PDF del cartel en un visor grande integrado.
 *
 * @package Pro
 */

get_header();
?>

<main id="primary" class="site-main container single-article-container">

    <?php
    while ( have_posts() ) :
        the_post();
        
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

        <article id="post-<?php the_ID(); ?>" <?php post_class('single-post-article cartel-article'); ?>>
            
            <!-- Breadcrumbs / Migas de Pan -->
            <nav class="breadcrumbs" aria-label="Breadcrumb" style="margin-bottom: 20px;">
                <ol>
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/edictos-y-carteles/' ) ); ?>">Carteles y Edictos</a></li>
                    <li><span aria-current="page"><?php the_title(); ?></span></li>
                </ol>
            </nav>

            <header class="entry-header">
                <h1 class="entry-title" style="margin-bottom: 10px;"><?php the_title(); ?></h1>
                
                <div class="entry-meta" style="margin-bottom: 20px;">
                    <span class="posted-on" style="color: var(--color-text-muted); font-size: 0.9rem;">
                        Publicado el <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date('j \d\e F \d\e Y'); ?></time>
                    </span>
                </div>
            </header>

            <div class="entry-content">
                <?php if ( ! empty( $pdf_url ) ) : ?>
                    <div class="pdf-container" style="width: 100%; height: 85vh; min-height: 600px; border: 1px solid var(--color-border); border-radius: var(--border-radius); overflow: hidden; margin-top: 20px; box-shadow: var(--shadow-sm); background: #eee;">
                        <iframe src="<?php echo esc_url( $pdf_url ); ?>" width="100%" height="100%" frameborder="0" style="width:100%; height:100%; display:block;"></iframe>
                    </div>
                    <div style="margin-top: 20px; text-align: center;">
                        <a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" class="button" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; background: var(--color-primary); color: #fff; border-radius: 6px; text-decoration: none; font-family: var(--font-ui); font-weight: 600; font-size: 1rem; transition: background 0.3s;">
                            <span class="material-symbols-outlined" aria-hidden="true">open_in_new</span>
                            Abrir documento completo en otra pestaña
                        </a>
                    </div>
                <?php else : ?>
                    <div style="padding: 40px; text-align: center; background: var(--color-bg-secondary); border-radius: var(--border-radius); border: 1px dashed var(--color-border);">
                        <p style="font-size: 1.1rem; color: var(--color-text-muted);">No se ha adjuntado ningún documento PDF válido para este cartel.</p>
                    </div>
                <?php endif; ?>
                
                <div style="margin-top: 30px;">
                    <?php
                    // Mostrar cualquier contenido extra de texto que el usuario haya añadido
                    the_content();
                    ?>
                </div>
            </div><!-- .entry-content -->

        </article><!-- #post-<?php the_ID(); ?> -->

    <?php endwhile; ?>

</main><!-- #primary -->

<?php
get_footer();
