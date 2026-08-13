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
 * Template Name: Página de Categoría Automática
 *
 * Plantilla para las páginas físicas que muestran las noticias de su misma categoría.
 *
 * @package Pro
 */

get_header();

// Obtenemos el slug de la página (ej: "deportes", "salud", "opinion")
global $post;
$cat_slug = $post->post_name;
$cat_name = get_the_title(); // Solo para mostrar en el título visualmente

$category = get_term_by( 'slug', $cat_slug, 'category' );

if ( ! $category ) {
    $category = get_term_by( 'name', $cat_name, 'category' );
}

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$args = array(
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => 12,
    // Desempate preciso: dos artículos en la misma fecha quedan ordenados por ID
    'orderby'             => array( 'date' => 'DESC', 'ID' => 'DESC' ),
    'ignore_sticky_posts' => 1,
    'paged'               => $paged,
);

if ( $category ) {
    // Auditoría fix: usar tax_query con include_children explícito.
    // category__in NO incluye hijos. cat SÍ, pero tax_query permite
    // controlar el comportamiento de manera explícita por slug.
    //
    // opinion y bienestar: no mezclar subcategorías (si tuviesen).
    // Resto (Monagas, Nacional, Sucesos, etc.): sí incluir hijos.
    $include_children = ! in_array(
        $category->slug,
        array( 'opinion', 'bienestar' ),
        true
    );

    $args['tax_query'] = array(
        array(
            'taxonomy'         => 'category',
            'field'            => 'term_id',
            'terms'            => array( $category->term_id ),
            'include_children' => $include_children,
            'operator'         => 'IN',
        ),
    );
} else {
    // Categoría no encontrada: devolver conjunto vacío.
    $args['post__in'] = array( 0 );
}

$query = new WP_Query( $args );
?>

<main id="primary" class="site-main container archive-container">

    <header class="page-header">
        <h1 class="page-title"><?php echo esc_html( $cat_name ); ?></h1>
    </header><!-- .page-header -->

    <!-- BANNER PATROCINADOR DE CATEGORÍA -->
    <?php
    get_template_part( 'template-parts/ads/category-sponsor', null, array(
        'cat_name' => $cat_name,
        'location' => 'category-top',
    ) );
    ?>

    <?php
    // MOSTRAR SUBCATEGORÍAS DE LA CATEGORÍA ACTUAL (Oculto a petición)
    /*
    if ( $category && $category->taxonomy === 'category' ) {
        $parent_id = ($category->category_parent == 0) ? $category->term_id : $category->category_parent;
        
        $subcategories = get_categories( array(
            'child_of'   => $parent_id,
            'hide_empty' => false,
        ) );

        if ( ! empty( $subcategories ) ) {
            echo '<div class="category-subnav"><ul class="subnav-list">';
            if ($parent_id != $category->term_id) {
                echo '<li><a href="' . esc_url( get_category_link( $parent_id ) ) . '">' . esc_html__( 'Todas', 'pro' ) . '</a></li>';
            } else {
                echo '<li class="current-cat"><a href="' . esc_url( get_category_link( $parent_id ) ) . '">' . esc_html__( 'Todas', 'pro' ) . '</a></li>';
            }

            foreach ( $subcategories as $subcat ) {
                $current_class = ($subcat->term_id == $category->term_id) ? 'current-cat' : '';
                echo '<li class="' . $current_class . '"><a href="' . esc_url( get_category_link( $subcat->term_id ) ) . '">' . esc_html( $subcat->name ) . '</a></li>';
            }
            echo '</ul></div>';
        }
    }
    */
    ?>

    <?php if ( $query->have_posts() ) : ?>

        <div class="category-grid-wrapper">
            <?php
            $post_count = 0;
            if ( $paged == 1 && $query->have_posts() ) {
                $query->the_post();
                $post_count++;
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('category-hero'); ?>>
                    <a href="<?php the_permalink(); ?>" class="post-thumbnail hero-thumbnail" aria-hidden="true" tabindex="-1">
                        <?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?>
                    </a>
                    <div class="hero-content">
                        <div class="post-meta">
                            <?php pro_post_categories( null, $cat_slug ); ?>
                            <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                        </div>
                        <h2 class="entry-title hero-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
                        <div class="entry-excerpt">
                            <?php echo wp_trim_words( get_the_excerpt(), 35, '...' ); ?>
                        </div>
                    </div>
                </article>
                <?php
            }
            ?>
            <div class="category-grid">
                <?php
                while ( $query->have_posts() ) :
                    $query->the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('card-post'); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>" class="post-thumbnail" aria-hidden="true" tabindex="-1">
                                <?php the_post_thumbnail( 'card-thumbnail', array( 'loading' => 'lazy' ) ); ?>
                            </a>
                        <?php endif; ?>
                        <div class="card-content">
                            <div class="post-meta">
                                <?php pro_post_categories( null, $cat_slug ); ?>
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
                wp_reset_postdata();
                ?>
            </div>
        </div>

        <?php if ( $query->max_num_pages > 1 && $paged < $query->max_num_pages ) : ?>
            <div class="infinite-scroll-trigger" data-cat-id="<?php echo esc_attr( $category ? $category->term_id : 0 ); ?>" data-current-page="<?php echo esc_attr( $paged ); ?>" data-max-pages="<?php echo esc_attr( $query->max_num_pages ); ?>">
                <div class="loading-spinner">Cargando más noticias...</div>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <section class="no-results not-found">
            <div class="page-content">
                <p><?php esc_html_e( 'Aún no hay noticias en esta sección.', 'pro' ); ?></p>
            </div>
        </section>
    <?php endif; ?>

</main><!-- #primary -->

<?php
get_footer();
