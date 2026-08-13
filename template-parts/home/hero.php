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
 * Hero Section Template
 * Displays the 3 latest posts in a hierarchical grid.
 */

$hero_args = array(
    'category_name'       => 'relevantes',
    'posts_per_page'      => 3,
    'post_status'         => 'publish',
    'orderby'             => 'date',
    'order'               => 'DESC',
    'ignore_sticky_posts' => 1,
);
$hero_query = new WP_Query( $hero_args );

if ( $hero_query->have_posts() ) : ?>
    <div class="hero-grid-layout">
        <?php
        $count = 0;
        while ( $hero_query->have_posts() ) : $hero_query->the_post();
            $count++;
            
            if ( $count === 1 ) :
                // Noticia Principal (Izquierda)
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('hero-main-post'); ?>>
                    <a href="<?php the_permalink(); ?>" class="post-thumbnail" aria-hidden="true" tabindex="-1">
                        <?php 
                        if (has_post_thumbnail()) {
                            the_post_thumbnail( 'hero-thumbnail', array( 'loading' => 'eager' ) );
                        } else {
                            echo '<div class="placeholder-image"><span>Foto</span></div>';
                        }
                        ?>
                    </a>
                    <div class="hero-content">
                        <?php if (function_exists('pro_post_categories')) pro_post_categories(null, 'relevantes'); ?>
                        <h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                        <div class="entry-summary">
                            <?php the_excerpt(); ?>
                        </div>
                        <div class="post-meta-footer">
                            <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                        </div>
                    </div>
                </article>
                <div class="hero-secondary-posts">
            <?php else :
                // Noticias Secundarias (Derecha)
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('hero-sub-post'); ?>>
                    <a href="<?php the_permalink(); ?>" class="post-thumbnail" aria-hidden="true" tabindex="-1">
                        <?php 
                        if (has_post_thumbnail()) {
                            the_post_thumbnail( 'medium', array( 'loading' => 'lazy' ) );
                        } else {
                            echo '<div class="placeholder-image"><span>Foto</span></div>';
                        }
                        ?>
                    </a>
                    <div class="hero-content">
                        <?php if (function_exists('pro_post_categories')) pro_post_categories(null, 'relevantes'); ?>
                        <h3 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h3>
                        <div class="post-meta-footer">
                            <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                        </div>
                    </div>
                </article>
                <?php
            endif;
            
        endwhile;
        
        if ( $count > 0 ) {
            // Cierra el contenedor secundario si al menos hubo 1 post
            echo '</div>'; 
        }
        ?>
    </div>
<?php endif; wp_reset_postdata(); ?>
