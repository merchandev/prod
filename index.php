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
 * La plantilla principal del tema Pro.
 *
 * @package Pro
 */

get_header();
?>

<main id="primary" class="site-main container">

    <?php if ( have_posts() ) : ?>

        <div class="home-layout-grid">
            
            <?php
            // Primera noticia (Héroe - Destacada)
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('hero-post'); ?>>
                <?php if ( has_post_thumbnail() ) : ?>
                    <a href="<?php the_permalink(); ?>" class="post-thumbnail" aria-hidden="true" tabindex="-1">
                        <?php the_post_thumbnail( 'hero-thumbnail', array( 'loading' => 'eager' ) ); // Eager load for LCP ?>
                    </a>
                <?php endif; ?>
                <div class="hero-content">
                        <?php pro_post_categories(); ?>
                    <h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                    <div class="entry-summary">
                        <?php the_excerpt(); ?>
                    </div>
                    <div class="post-meta-footer">
                        Por <span class="author"><?php the_author(); ?></span> &bull; 
                        <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                    </div>
                </div>
            </article>

            <?php if ( is_active_sidebar( 'ad-in-feed' ) ) : ?>
                <div class="home-ad-break">
                    <?php dynamic_sidebar( 'ad-in-feed' ); ?>
                </div>
            <?php endif; ?>

            <div class="secondary-posts-grid">
                <?php
                // Resto de las noticias en formato Card
                while ( have_posts() ) :
                    the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('card-post'); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>" class="post-thumbnail" aria-hidden="true" tabindex="-1">
                                <?php the_post_thumbnail( 'card-thumbnail', array( 'loading' => 'lazy' ) ); ?>
                            </a>
                        <?php endif; ?>
                        <div class="card-content">
                            <div class="post-meta">
                                <?php pro_post_categories(); ?>
                            </div>
                            <h3 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h3>
                        </div>
                    </article>
                    <?php
                endwhile;
                ?>
            </div><!-- .secondary-posts-grid -->

        </div><!-- .home-layout-grid -->

        <?php global $wp_query; if ( $wp_query->max_num_pages > 1 ) : ?>
            <div class="load-more-container text-center">
                <button id="load-more-btn" class="btn-primary">Cargar más noticias</button>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <p><?php esc_html_e( 'No hay noticias publicadas por el momento.', 'pro' ); ?></p>
    <?php endif; ?>

</main><!-- #primary -->

<?php
get_footer();
