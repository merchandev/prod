<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Dompdf\Dompdf;
use Dompdf\Options;

final class Espressivo_PDF_Generator {
    /**
     * @param array<string, mixed> $report
     */
    public function download( array $report ): void {
        if ( ! class_exists( Dompdf::class ) ) {
            wp_die(
                esc_html__( 'Dompdf no está disponible.', 'espressivo-reportes' ),
                '',
                array( 'response' => 500 )
            );
        }

        $temp_dir = trailingslashit( get_temp_dir() ) . 'espressivo-reportes-pdf';
        wp_mkdir_p( $temp_dir );

        $options = new Options();
        $options->set( 'defaultFont', 'DejaVu Sans' );
        $options->set( 'isRemoteEnabled', false );
        $options->set( 'isPhpEnabled', false );
        $options->set( 'isFontSubsettingEnabled', true );
        $options->set( 'tempDir', $temp_dir );
        $options->set( 'fontCache', $temp_dir );
        $options->set( 'chroot', ESPRESSIVO_REPORTES_DIR );

        $dompdf = new Dompdf( $options );
        $dompdf->setPaper( 'A4', 'landscape' );
        $dompdf->loadHtml( $this->render_template( $report ), 'UTF-8' );
        $dompdf->render();

        $canvas       = $dompdf->getCanvas();
        $font_metrics = $dompdf->getFontMetrics();
        $font         = $font_metrics->getFont( 'DejaVu Sans', 'normal' );

        if ( $font ) {
            $footer_y = $canvas->get_height() - 22;

            $canvas->page_text(
                38,
                $footer_y,
                'Diario El Oriental - Reporte editorial interno',
                $font,
                7.5,
                array( 0.34, 0.36, 0.39 )
            );

            $canvas->page_text(
                $canvas->get_width() - 132,
                $footer_y,
                'Página {PAGE_NUM} de {PAGE_COUNT}',
                $font,
                7.5,
                array( 0.34, 0.36, 0.39 )
            );
        }

        $pdf      = $dompdf->output();
        $filename = $this->build_filename( $report );

        while ( ob_get_level() > 0 ) {
            ob_end_clean();
        }

        nocache_headers();
        header( 'Content-Type: application/pdf' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Content-Length: ' . strlen( $pdf ) );
        header( 'X-Content-Type-Options: nosniff' );

        echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        do_action(
            'Espressivo_report_exported',
            (int) $report['user']->ID,
            $report['range'],
            (int) $report['total']
        );

        exit;
    }

    /**
     * @param array<string, mixed> $report
     */
    private function render_template( array $report ): string {
        $logo_path     = get_template_directory() . '/assets/image/LOGO NEGRO.png';
        $logo_data_uri = '';

        if ( is_readable( $logo_path ) ) {
            $logo_data_uri = 'data:image/png;base64,' . base64_encode(
                (string) file_get_contents( $logo_path )
            );
        }

        $generated_at = wp_date( 'd/m/Y H:i', null, wp_timezone() );
        $site_name    = get_bloginfo( 'name' );
        $site_url     = home_url( '/' );

        ob_start();
        require ESPRESSIVO_REPORTES_DIR . 'templates/pdf-report.php';

        return (string) ob_get_clean();
    }

    /**
     * @param array<string, mixed> $report
     */
    private function build_filename( array $report ): string {
        $user_name = sanitize_title( (string) $report['user']->display_name );
        $start     = $report['range']['start']->format( 'Y-m-d' );
        $end       = $report['range']['end']->format( 'Y-m-d' );

        return sanitize_file_name(
            sprintf(
                'reporte-editorial-%s-%s-%s.pdf',
                $user_name ?: 'perfil',
                $start,
                $end
            )
        );
    }
}
