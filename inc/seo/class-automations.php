<?php
namespace SSIVO_SEO\Includes;

class Automations {
    private $database;

    public function __construct( Database $database ) {
        $this->database = $database;
        add_action( 'save_post', [ $this, 'handle_save' ], 10, 2 );
    }

    public function handle_save( $post_id, $post ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }


        $raw_content = apply_filters( 'ssivo_seo_before_meta_extraction', $post->post_content, $post );
        
        $seo_data = [
            'meta_title' => sanitize_text_field( get_the_title( $post->ID ) ),
            'meta_desc'  => $this->extract_pure_text( $raw_content, 155 )
        ];

        $this->database->save_seo_data( $post->ID, $seo_data );
        $this->ensure_featured_image( $post );
    }

    private function extract_pure_text( $content, $max_length ) {
        if ( empty( $content ) ) return '';
        $text = preg_replace( '/<[^>]*>?/m', '', $content ); // Eliminar etiquetas HTML
        $text = strip_shortcodes( $text );
        $text = wp_strip_all_tags( $text );
        $text = preg_replace( '/\s+/', ' ', $text );
        $text = trim( $text );
        return mb_strlen( $text, 'UTF-8' ) > $max_length ? mb_strimwidth( $text, 0, $max_length, '...', 'UTF-8' ) : $text;
    }

    private function ensure_featured_image( $post ) {
        if ( ! has_post_thumbnail( $post->ID ) ) {
            preg_match( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches );
            if ( ! empty( $matches[1] ) ) {
                $attachment_id = attachment_url_to_postid( $matches[1] );
                if ( $attachment_id ) set_post_thumbnail( $post->ID, $attachment_id );
            }
        }
    }
}
