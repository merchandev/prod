<?php
namespace SSIVO_SEO\Includes;

class FrontendMeta {
    private $database;

    public function __construct( Database $database ) {
        $this->database = $database;
        // Enganchar en wp_head con prioridad 1 para aparecer arriba
        add_action( 'wp_head', [ $this, 'inject_meta_tags' ], 1 );
        
        // Deshabilitar el título por defecto de WordPress para evitar duplicados
        remove_action( 'wp_head', '_wp_render_title_tag', 1 );
    }

    public function inject_meta_tags() {
        // Configuración global
        $site_name    = get_bloginfo( 'name' );
        $suffix_opt   = get_option( 'ssivo_seo_default_title', $site_name );
        $suffix_str   = ! empty( $suffix_opt ) ? ' | ' . $suffix_opt : '';
        $default_img  = get_option( 'ssivo_seo_default_image', '' );
        $canonical    = '';

        // ── Páginas de archivo / portada ─────────────────────────────────────
        if ( ! is_singular() ) {
            if ( is_home() || is_front_page() ) {
                $title     = $site_name;
                $desc      = get_bloginfo( 'description' );
                $canonical = esc_url( home_url( '/' ) );
                $og_type   = 'website';
            } elseif ( is_category() ) {
                $cat       = get_queried_object();
                $title     = esc_html( $cat->name ) . $suffix_str;
                $desc      = ! empty( $cat->description ) ? esc_attr( $cat->description ) : '';
                $canonical = esc_url( get_category_link( $cat->term_id ) );
                $og_type   = 'website';
            } elseif ( is_tag() ) {
                $tag       = get_queried_object();
                $title     = esc_html( $tag->name ) . $suffix_str;
                $desc      = ! empty( $tag->description ) ? esc_attr( $tag->description ) : '';
                $canonical = esc_url( get_tag_link( $tag->term_id ) );
                $og_type   = 'website';
            } elseif ( is_author() ) {
                $author    = get_queried_object();
                $title     = esc_html( $author->display_name ) . $suffix_str;
                $desc      = '';
                $canonical = esc_url( get_author_posts_url( $author->ID ) );
                $og_type   = 'website';
            } elseif ( is_search() ) {
                $title     = esc_html( sprintf( 'Búsqueda: %s', get_search_query() ) ) . $suffix_str;
                $desc      = '';
                $canonical = esc_url( get_search_link() );
                $og_type   = 'website';
            } else {
                $title     = $site_name;
                $desc      = get_bloginfo( 'description' );
                $canonical = esc_url( home_url( '/' ) );
                $og_type   = 'website';
            }

            $image_url = $default_img;

            echo "<title>{$title}</title>\n";
            if ( ! empty( $desc ) ) {
                echo "<meta name=\"description\" content=\"{$desc}\" />\n";
            }
            echo "<link rel=\"canonical\" href=\"{$canonical}\" />\n";
            // Open Graph
            echo "<meta property=\"og:type\" content=\"{$og_type}\" />\n";
            echo "<meta property=\"og:title\" content=\"{$title}\" />\n";
            echo "<meta property=\"og:url\" content=\"{$canonical}\" />\n";
            echo "<meta property=\"og:site_name\" content=\"" . esc_attr( $site_name ) . "\" />\n";
            if ( ! empty( $desc ) ) {
                echo "<meta property=\"og:description\" content=\"{$desc}\" />\n";
            }
            if ( ! empty( $image_url ) ) {
                echo "<meta property=\"og:image\" content=\"" . esc_url( $image_url ) . "\" />\n";
            }
            // Twitter Card
            echo "<meta name=\"twitter:card\" content=\"summary_large_image\" />\n";
            echo "<meta name=\"twitter:title\" content=\"{$title}\" />\n";
            if ( ! empty( $desc ) ) {
                echo "<meta name=\"twitter:description\" content=\"{$desc}\" />\n";
            }
            if ( ! empty( $image_url ) ) {
                echo "<meta name=\"twitter:image\" content=\"" . esc_url( $image_url ) . "\" />\n";
            }
            return;
        }

        // ── Entradas / páginas singulares ────────────────────────────────────
        global $post;
        $seo_data = $this->database->get_seo_data( $post->ID );

        // Overrides personalizados del Metabox
        $custom_title = get_post_meta( $post->ID, '_ssivo_seo_custom_title', true );
        $custom_desc  = get_post_meta( $post->ID, '_ssivo_seo_custom_desc', true );

        // Determinar Título Final
        if ( ! empty( $custom_title ) ) {
            $final_title = esc_html( $custom_title );
        } elseif ( $seo_data && ! empty( $seo_data['meta_title'] ) ) {
            $final_title = esc_html( $seo_data['meta_title'] );
        } else {
            $final_title = get_the_title();
        }
        $final_title .= $suffix_str;

        // Determinar Descripción Final
        if ( ! empty( $custom_desc ) ) {
            $final_desc = esc_attr( $custom_desc );
        } elseif ( $seo_data && ! empty( $seo_data['meta_desc'] ) ) {
            $final_desc = esc_attr( $seo_data['meta_desc'] );
        } else {
            $final_desc = '';
        }

        // Determinar Imagen
        $image_url = $default_img;
        if ( has_post_thumbnail( $post->ID ) ) {
            $image_url = get_the_post_thumbnail_url( $post->ID, 'large' );
        }

        // Determinar URL canónica
        $canonical = esc_url( get_permalink( $post->ID ) );

        // og:type: article para posts, website para pages
        $og_type = ( 'page' === $post->post_type ) ? 'website' : 'article';

        // ── Imprimir etiquetas ───────────────────────────────────────────────
        echo "<title>{$final_title}</title>\n";
        if ( ! empty( $final_desc ) ) {
            echo "<meta name=\"description\" content=\"{$final_desc}\" />\n";
        }
        echo "<link rel=\"canonical\" href=\"{$canonical}\" />\n";

        // Open Graph
        echo "<meta property=\"og:type\" content=\"{$og_type}\" />\n";
        echo "<meta property=\"og:title\" content=\"{$final_title}\" />\n";
        echo "<meta property=\"og:url\" content=\"{$canonical}\" />\n";
        echo "<meta property=\"og:site_name\" content=\"" . esc_attr( $site_name ) . "\" />\n";
        if ( ! empty( $final_desc ) ) {
            echo "<meta property=\"og:description\" content=\"{$final_desc}\" />\n";
        }
        if ( ! empty( $image_url ) ) {
            echo "<meta property=\"og:image\" content=\"" . esc_url( $image_url ) . "\" />\n";
        }

        // Twitter Card
        echo "<meta name=\"twitter:card\" content=\"summary_large_image\" />\n";
        echo "<meta name=\"twitter:title\" content=\"{$final_title}\" />\n";
        if ( ! empty( $final_desc ) ) {
            echo "<meta name=\"twitter:description\" content=\"{$final_desc}\" />\n";
        }
        if ( ! empty( $image_url ) ) {
            echo "<meta name=\"twitter:image\" content=\"" . esc_url( $image_url ) . "\" />\n";
        }
    }
}
