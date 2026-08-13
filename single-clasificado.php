<?php
/**
 * Clasificado individual.
 *
 * @package Espressivo
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main
    id="primary"
    class="site-main classified-single"
>
    <div class="classified-single__container">
        <?php while ( have_posts() ) : ?>
            <?php the_post(); ?>

            <article <?php post_class( 'classified-detail' ); ?>>
                <header class="classified-detail__header">
                    <?php
                    $types = get_the_terms(
                        get_the_ID(),
                        'tipo_clasificado'
                    );

                    if (
                        ! empty( $types )
                        && ! is_wp_error( $types )
                    ) :
                        ?>
                        <div class="classified-detail__types">
                            <?php foreach ( $types as $type ) : ?>
                                <a href="<?php
                                echo esc_url(
                                    add_query_arg(
                                        'tipo',
                                        $type->slug,
                                        get_post_type_archive_link(
                                            'clasificado'
                                        )
                                    )
                                );
                                ?>">
                                    <?php echo esc_html( $type->name ); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <h1>
                        <?php the_title(); ?>
                    </h1>

                    <p class="classified-detail__date">
                        <?php
                        printf(
                            esc_html__(
                                'Publicado el %s',
                                'pro'
                            ),
                            esc_html( get_the_date() )
                        );
                        ?>
                    </p>
                </header>

                <div class="classified-detail__content">
                    <?php the_content(); ?>
                </div>

                <footer class="classified-detail__footer">
                    <a
                        href="<?php
                        echo esc_url(
                            get_post_type_archive_link(
                                'clasificado'
                            )
                        );
                        ?>"
                    >
                        <?php
                        esc_html_e(
                            '← Volver a clasificados',
                            'pro'
                        );
                        ?>
                    </a>
                </footer>
            </article>
        <?php endwhile; ?>
    </div>
</main>

<?php
get_footer();
