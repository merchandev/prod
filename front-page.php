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
 * Portada personalizada estilo editorial (WaPo Style).
 *
 * @package Pro
 */

get_header();
?>

<main id="primary" class="site-main container">

    <!-- SECCIÓN UNIFICADA: NOTICIA PRINCIPAL ARRIBA Y REJA (60% NOTICIAS / 40% PORTADA) ABAJO -->
    <?php
    $portada_img = get_option( 'pro_portada_actual' );
    if ( ! $portada_img ) {
        $portada_img = get_template_directory_uri() . '/screenshot.png';
    }

    // Primero intentamos la categoría "relevantes"; si no hay, usamos los últimos posts
    $relevant_query = new WP_Query( array(
        'category_name'       => 'relevantes',
        'posts_per_page'      => 3,
        'post_status'         => 'publish',
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => 1,
    ) );

    // Fallback: si no hay posts en "relevantes", tomar los 3 más recientes
    if ( ! $relevant_query->have_posts() ) {
        wp_reset_postdata();
        $relevant_query = new WP_Query( array(
            'posts_per_page'      => 3,
            'post_status'         => 'publish',
            'orderby'             => 'date',
            'order'               => 'DESC',
            'ignore_sticky_posts' => 1,
        ) );
    }
    ?>

    <?php if ( $relevant_query->have_posts() ) : ?>
        
        <?php 
        // 1. Extraemos el primer post para la noticia principal que va sola arriba
        $relevant_query->the_post();
        ?>
        <div class="home-main-news-wrapper" style="margin-top: 30px; margin-bottom: 40px; width: 100%;">
            <article id="post-<?php the_ID(); ?>" <?php post_class('hero-main-post'); ?>>
                <a href="<?php the_permalink(); ?>" class="post-thumbnail" aria-hidden="true" tabindex="-1">
                    <?php 
                    if ( has_post_thumbnail() ) {
                        the_post_thumbnail( 'hero-thumbnail', array( 'loading' => 'eager' ) );
                    } else {
                        echo '<div class="placeholder-image"><span>Foto</span></div>';
                    }
                    ?>
                </a>
                <div class="hero-content">
                    <?php if ( function_exists('pro_post_categories') ) : ?>
                        <?php pro_post_categories(); ?>
                    <?php endif; ?>
                    <h2 class="entry-title">
                        <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                    </h2>
                    <div class="entry-summary">
                        <?php the_excerpt(); ?>
                    </div>
                    <div class="post-meta-footer">
                        <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                    </div>
                </div>
            </article>
        </div>

        <!-- 2. Reja de abajo: 60% las otras 2 noticias, 40% la Portada -->
        <div class="home-hero-portada-grid">
            <!-- Columna de Noticias (60%) -->
            <div class="hero-news-column">
                <?php 
                while ( $relevant_query->have_posts() ) : $relevant_query->the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('hero-sub-post'); ?>>
                        <div class="hero-content">
                            <?php if ( function_exists('pro_post_categories') ) : ?>
                                <?php pro_post_categories(); ?>
                            <?php endif; ?>
                            <h3 class="entry-title">
                                <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
                            </h3>
                            <div class="post-meta-footer">
                                <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
                            </div>
                        </div>
                    </article>
                    <?php
                endwhile;
                ?>
            </div>

            <!-- Columna de Portada (40%) -->
            <div class="hero-portada-column">
                <div class="portada-header-badge">
                    <h4 class="portada-main-title">Portada del día</h4>
                </div>
                
                <div class="home-portada-card card-portada" data-full-url="<?php echo esc_url($portada_img); ?>">
                    <div class="home-portada-thumbnail">
                        <img src="<?php echo esc_url($portada_img); ?>" alt="Portada del Día" loading="eager">
                        <div class="portada-overlay">
                            <div class="portada-overlay-button">
                                <span class="material-symbols-outlined overlay-lupa-icon" aria-hidden="true">zoom_in</span>
                                <span>Clic para ver</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php 
        wp_reset_postdata();
    endif; 
    ?>


    <!-- PUBLICIDAD BELOW HERO -->
    <?php get_template_part('template-parts/ads/in-feed', null, array('location' => 'below-hero')); ?>

    <!-- WAPO CATEGORY SECTIONS -->
    <div class="wapo-sections-container">
        
        <?php
        // Función Helper interna para evitar repetir el código de placeholders
        if ( ! function_exists( 'pro_render_placeholder_main' ) ) {
            function pro_render_placeholder_main( $title = "Título de Noticia Principal" ) {
                ?>
                <article class="wapo-main-article placeholder-mode">
                    <div class="post-thumbnail placeholder-image">
                        <span>Espacio Foto</span>
                    </div>
                    <div class="wapo-main-content">
                        <h3 class="entry-title placeholder-text"><?php echo esc_html($title); ?></h3>
                        <div class="entry-summary placeholder-text-small">
                            Este es un texto de relleno que muestra cómo se verá el extracto de la noticia.
                        </div>
                    </div>
                </article>
                <?php
            }
        }
        if ( ! function_exists( 'pro_render_placeholder_side' ) ) {
            function pro_render_placeholder_side($count = 3) {
                echo '<div class="wapo-side-articles placeholder-mode">';
                for($i=1; $i<=$count; $i++) {
                    echo '<article class="wapo-list-item">';
                    echo '<h4 class="entry-title placeholder-text">Titular secundario de noticia ' . $i . '</h4>';
                    echo '</article>';
                }
                echo '</div>';
            }
        }
        ?>

        <!-- ZONA PREMIUM (Diseño Principal) -->
        <div class="zone-premium">
            <?php get_template_part('template-parts/home/premium'); ?>
        </div>

        <!-- PUBLICIDAD IN-FEED 1 -->
        <?php get_template_part('template-parts/ads/in-feed', null, array('location' => 'in-feed-1')); ?>

        <!-- ZONA LOCAL (Maturín y Monagas) -->
        <?php get_template_part('template-parts/home/local'); ?>

        <!-- PUBLICIDAD IN-FEED 2 -->
        <?php get_template_part('template-parts/ads/in-feed', null, array('location' => 'in-feed-2')); ?>

        <!-- ZONA COLUMNAS (Secciones Secundarias) -->
        <?php get_template_part('template-parts/home/secondary'); ?>

        <!-- BANNER CARTELES Y EDICTOS -->
        <?php get_template_part('template-parts/home/carteles'); ?>

    </div>

</main><!-- #primary -->

<?php
get_footer();
