<?php
/** @var array<string, mixed> $report */
/** @var string $logo_data_uri */
/** @var string $generated_at */
/** @var string $site_name */
/** @var string $site_url */
/** @var string $report_code */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user          = $report['user'];
$range         = $report['range'];
$items         = $report['items'];
$total         = (int) $report['total'];
$status_counts = $report['status_counts'];
$type_counts   = $report['type_counts'];
$status_labels = array();

foreach ( get_post_stati( array(), 'objects' ) as $status_key => $status_object ) {
    $status_labels[ $status_key ] = $status_object->label;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo esc_html( 'Reporte editorial - ' . $user->display_name ); ?></title>
    <style>
        @page { margin: 29mm 12mm 17mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #17191d;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 8.8pt;
            line-height: 1.36;
        }
        table { width: 100%; border-collapse: collapse; }
        .page-header {
            position: fixed;
            top: -23mm;
            right: 0;
            left: 0;
            height: 19mm;
            border-bottom: 2px solid #ed1c24;
        }
        .header-table { height: 18mm; }
        .header-logo { width: 58%; vertical-align: middle; }
        .header-logo img { width: 172px; height: auto; }
        .header-title { width: 42%; text-align: right; vertical-align: middle; }
        .header-title strong { display: block; font-size: 12.5pt; }
        .header-title span { color: #6b6f75; font-size: 7.5pt; }

        .report-heading { margin-bottom: 10px; }
        .eyebrow {
            color: #b20f16;
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 1.1px;
            text-transform: uppercase;
        }
        .report-heading h1 { margin: 3px 0; font-size: 21pt; line-height: 1.12; }
        .report-heading p { margin: 0; color: #666a70; }

        .summary-table {
            margin: 12px 0 14px;
            border-spacing: 6px 0;
            border-collapse: separate;
        }
        .summary-card {
            width: 25%;
            padding: 9px 10px;
            border: 1px solid #e1e3e6;
            border-top: 3px solid #f4bd24;
            background: #fafafa;
            vertical-align: top;
        }
        .summary-card.red { border-top-color: #ed1c24; }
        .summary-card.dark { border-top-color: #191b1e; }
        .summary-label {
            display: block;
            color: #686c72;
            font-size: 7.1pt;
            font-weight: bold;
            letter-spacing: .45px;
            text-transform: uppercase;
        }
        .summary-value { display: block; margin-top: 3px; font-size: 10.7pt; font-weight: bold; }
        .summary-line {
            margin: -2px 0 10px;
            padding: 7px 9px;
            border-left: 3px solid #f4bd24;
            background: #fff8df;
            color: #4d5055;
            font-size: 7.8pt;
        }
        .summary-line + .summary-line { margin-top: -7px; }

        .report-table { table-layout: fixed; }
        .report-table thead { display: table-header-group; }
        .report-table tr { page-break-inside: avoid; }
        .report-table th {
            padding: 7px 6px;
            border: 1px solid #202328;
            background: #202328;
            color: #fff;
            font-size: 7.1pt;
            letter-spacing: .25px;
            text-align: left;
            text-transform: uppercase;
        }
        .report-table td {
            padding: 6.5px 6px;
            border: 1px solid #d9dce0;
            vertical-align: top;
        }
        .report-table tbody tr:nth-child(even) td { background: #f7f7f8; }
        .col-number { width: 5%; text-align: center; }
        .col-date { width: 14%; }
        .col-title { width: 39%; }
        .col-status { width: 13%; }
        .col-link { width: 29%; }
        .item-title { color: #15171a; font-weight: bold; }
        .item-type { display: block; margin-top: 3px; color: #777b81; font-size: 7pt; }
        .status-badge {
            display: inline-block;
            padding: 3px 6px;
            border: 1px solid #d4d6da;
            border-radius: 8px;
            background: #fff;
            font-size: 6.7pt;
            font-weight: bold;
        }
        .link-button { color: #b20f16; font-weight: bold; text-decoration: none; }
        .display-url {
            display: block;
            margin-top: 3px;
            color: #64686e;
            font-size: 6.4pt;
            word-wrap: break-word;
        }
        .empty-state {
            padding: 30px;
            border: 1px dashed #bfc2c7;
            background: #fafafa;
            color: #5f6368;
            text-align: center;
        }
        .legal-note { margin-top: 11px; color: #74787e; font-size: 6.7pt; }
    </style>
</head>
<body>
    <div class="page-header">
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    <?php if ( $logo_data_uri ) : ?>
                        <img src="<?php echo esc_attr( $logo_data_uri ); ?>" alt="Diario El Oriental">
                    <?php else : ?>
                        <strong><?php echo esc_html( $site_name ); ?></strong>
                    <?php endif; ?>
                </td>
                <td class="header-title">
                    <strong><?php esc_html_e( 'Reporte editorial', 'pro' ); ?></strong>
                    <span><?php echo esc_html( $range['label'] ); ?></span>
                </td>
            </tr>
        </table>
    </div>

    <section class="report-heading">
        <span class="eyebrow"><?php esc_html_e( 'Control de producción de contenido', 'pro' ); ?></span>
        <h1><?php echo esc_html( $user->display_name ); ?></h1>
        <p><?php echo esc_html( $report['role_label'] . ' · ' . $range['label'] ); ?></p>
    </section>

    <table class="summary-table">
        <tr>
            <td class="summary-card red">
                <span class="summary-label"><?php esc_html_e( 'Perfil', 'pro' ); ?></span>
                <span class="summary-value"><?php echo esc_html( $user->display_name ); ?></span>
            </td>
            <td class="summary-card">
                <span class="summary-label"><?php esc_html_e( 'Registros', 'pro' ); ?></span>
                <span class="summary-value"><?php echo esc_html( number_format_i18n( $total ) ); ?></span>
            </td>
            <td class="summary-card dark">
                <span class="summary-label"><?php esc_html_e( 'Período', 'pro' ); ?></span>
                <span class="summary-value"><?php echo esc_html( $range['label'] ); ?></span>
            </td>
            <td class="summary-card">
                <span class="summary-label"><?php esc_html_e( 'Generado', 'pro' ); ?></span>
                <span class="summary-value"><?php echo esc_html( $generated_at ); ?></span>
            </td>
        </tr>
    </table>

    <?php if ( ! empty( $status_counts ) ) : ?>
        <div class="summary-line">
            <strong><?php esc_html_e( 'Por estado:', 'pro' ); ?></strong>
            <?php
            $parts = array();
            foreach ( $status_counts as $status_key => $count ) {
                $parts[] = sprintf(
                    '%s: %s',
                    $status_labels[ $status_key ] ?? ucfirst( (string) $status_key ),
                    number_format_i18n( (int) $count )
                );
            }
            echo esc_html( implode( ' · ', $parts ) );
            ?>
        </div>
    <?php endif; ?>

    <?php if ( ! empty( $type_counts ) ) : ?>
        <div class="summary-line">
            <strong><?php esc_html_e( 'Por tipo:', 'pro' ); ?></strong>
            <?php
            $parts = array();
            foreach ( $type_counts as $post_type => $count ) {
                $object  = get_post_type_object( (string) $post_type );
                $label   = $object ? $object->labels->name : (string) $post_type;
                $parts[] = sprintf( '%s: %s', $label, number_format_i18n( (int) $count ) );
            }
            echo esc_html( implode( ' · ', $parts ) );
            ?>
        </div>
    <?php endif; ?>

    <?php if ( empty( $items ) ) : ?>
        <div class="empty-state">
            <strong><?php esc_html_e( 'No se encontraron registros en el período seleccionado.', 'pro' ); ?></strong>
        </div>
    <?php else : ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th class="col-number">#</th>
                    <th class="col-date"><?php esc_html_e( 'Fecha de carga', 'pro' ); ?></th>
                    <th class="col-title"><?php esc_html_e( 'Título', 'pro' ); ?></th>
                    <th class="col-status"><?php esc_html_e( 'Estado', 'pro' ); ?></th>
                    <th class="col-link"><?php esc_html_e( 'Enlace', 'pro' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $items as $index => $item ) : ?>
                    <tr>
                        <td class="col-number"><?php echo esc_html( (string) ( $index + 1 ) ); ?></td>
                        <td class="col-date"><?php echo esc_html( (string) $item['uploaded_at'] ); ?></td>
                        <td class="col-title">
                            <span class="item-title"><?php echo esc_html( (string) $item['title'] ); ?></span>
                            <span class="item-type"><?php echo esc_html( (string) $item['content_type'] ); ?></span>
                        </td>
                        <td class="col-status">
                            <span class="status-badge"><?php echo esc_html( (string) $item['status'] ); ?></span>
                        </td>
                        <td class="col-link">
                            <?php if ( ! empty( $item['url'] ) ) : ?>
                                <a class="link-button" href="<?php echo esc_url( (string) $item['url'] ); ?>">
                                    <?php echo esc_html( (string) $item['link_label'] ); ?>
                                </a>
                                <span class="display-url"><?php echo esc_html( (string) $item['display_url'] ); ?></span>
                            <?php else : ?>
                                <span><?php esc_html_e( 'Sin enlace disponible', 'pro' ); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p class="legal-note">
        <?php
        echo esc_html(
            sprintf(
                /* translators: 1: code, 2: website, 3: URL. */
                __( 'Código %1$s · Documento interno generado por %2$s · %3$s. Los enlaces conservan los permisos definidos en WordPress.', 'pro' ),
                $report_code,
                $site_name,
                $site_url
            )
        );
        ?>
    </p>
</body>
</html>
