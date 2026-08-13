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
 * Plantilla para mostrar los archivos de Categoría.
 *
 * @package Pro
 */

get_header();
?>

<main id="primary" class="site-main container archive-container">

    <header class="page-header">
        <?php
        the_archive_title( '<h1 class="page-title">', '</h1>' );
        the_archive_description( '<div class="archive-description">', '</div>' );
        ?>
    </header><!-- .page-header -->

    <!-- BANNER PATROCINADOR DE CATEGORÍA -->
    <?php
    $current_cat_name = is_category() ? single_cat_title( '', false ) : '';
    get_template_part( 'template-parts/ads/category-sponsor', null, array(
        'cat_name' => $current_cat_name,
        'location' => 'category-top',
    ) );
    ?>

    <?php
    // MOSTRAR SUBCATEGORÍAS DE LA CATEGORÍA ACTUAL
    $current_category = get_queried_object();
    if ( $current_category && $current_category->taxonomy === 'category' ) {
        $parent_id = ($current_category->category_parent == 0) ? $current_category->term_id : $current_category->category_parent;
        
        $subcats = get_categories( array(
            'child_of'   => $parent_id,
            'hide_empty' => false,
        ) );

        if ( ! empty( $subcats ) ) {
            echo '<div class="category-subnav"><ul class="subnav-list">';
            
            // Link to parent (Todas)
            $parent_cat = get_category($parent_id);
            $active_class = ($current_category->term_id == $parent_id) ? 'active' : '';
            echo '<li><a href="' . esc_url( get_category_link( $parent_id ) ) . '" class="' . $active_class . '">' . esc_html__( 'Todas', 'pro' ) . '</a></li>';
            
            foreach ( $subcats as $sc ) {
                $active = ($current_category->term_id == $sc->term_id) ? 'active' : '';
                echo '<li><a href="' . esc_url( get_category_link( $sc->term_id ) ) . '" class="' . $active . '">' . esc_html( $sc->name ) . '</a></li>';
            }
            echo '</ul></div>';
        }
    }
    ?>

    <?php if ( have_posts() ) : ?>

        <div class="category-grid">
            <?php
            /* Iniciar el Loop */
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
                            <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                        </div>
                        <h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                        <div class="entry-excerpt">
                            <?php echo wp_trim_words( get_the_excerpt(), 20, '...' ); ?>
                        </div>
                    </div>
                </article>
                <?php
            endwhile;
            ?>
        </div>

        <?php global $wp_query; if ( $wp_query->max_num_pages > 1 ) : ?>
            <div class="load-more-container text-center">
                <button id="load-more-btn" class="btn-primary">Cargar más noticias</button>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <section class="no-results not-found">
            <div class="page-content">
                <p><?php esc_html_e( 'Parece que no podemos encontrar lo que buscas. Tal vez una búsqueda ayude.', 'pro' ); ?></p>
                <?php get_search_form(); ?>
            </div>
        </section>
    <?php endif; ?>

</main><!-- #primary -->

<?php
get_footer();
