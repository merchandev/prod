<?php
/**
 * @author Arturo Merchan | Merchan.Dev | Espressivo Venezuela,C.A
 * 
 * ADVERTENCIA LEGAL:
 * Queda totalmente prohibida su reproduccion, edicion, venta, propaganda, alteracion 
 * o cualquier otra accion que de una u otra forma violente la propiedad intelectual, 
 * material y digital de este proyecto. Esta infraccion esta prohibida y penada por la ley.
 */?>
    </div><!-- /#swup -->

    <footer id="colophon" class="site-footer">
        <div class="footer-widgets container">
            <div class="footer-grid">
                <div class="footer-about">
                    <h3 class="footer-heading">Más información</h3>
                    <ul class="footer-category-list">
                        <li><a href="/contacto/">Contacto</a></li>
                        <li><a href="/clasificados/">Clasificados</a></li>
                        <li><a href="https://chat.whatsapp.com/CPuESpw6FJYEoKT1K3BcPP" target="_blank" rel="noopener noreferrer">Grupo de WhatsApp</a></li>
                        <li><a href="https://news.google.com/u/0/publications/CAAqBwgKMK70vAswu4_UAw?hl=es-419&gl=VE&ceid=VE:es-419" target="_blank" rel="noopener noreferrer">Google Noticias</a></li>
                        <li><a href="/relevantes">Relevantes</a></li>
                    </ul>
                </div>

                <div class="footer-categories">
                    <h3 class="footer-heading">Secciones Populares</h3>
                    <ul class="footer-category-list">
                        <?php
                        $top_cats = get_categories( array(
                            'orderby'    => 'count',
                            'order'      => 'DESC',
                            'number'     => 5,
                            'hide_empty' => false, // Mostrar incluso si no tienen noticias aún
                            'exclude'    => get_option( 'default_category' ) // Excluir "Sin categoría"
                        ) );
                        
                        if ( ! empty( $top_cats ) ) {
                            foreach ( $top_cats as $cat ) {
                                if ( $cat->slug === 'local' ) continue;
                                echo '<li><a href="' . esc_url( home_url( '/' . $cat->slug . '/' ) ) . '">' . esc_html( $cat->name ) . '</a></li>';
                            }
                        } else {
                            echo '<li class="empty-cats">Sin categorías</li>';
                        }
                        ?>
                    </ul>
                </div>

                <div class="footer-social-section">
                    <h3 class="footer-heading">Redes Sociales</h3>
                    <div class="social-icons">
                        <a href="#" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.04c-5.5 0-10 4.48-10 10.02 0 5 3.66 9.15 8.44 9.9v-7H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.23.19 2.23.19v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.45 2.9h-2.33v7a10 10 0 0 0 8.44-9.9c0-5.54-4.5-10.02-10-10.02z"/></svg>
                        </a>
                        <a href="#" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0-2.88 0 1.44 1.44 0 0 0 2.88 0z"/></svg>
                        </a>
                        <a href="https://chat.whatsapp.com/CPuESpw6FJYEoKT1K3BcPP" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 0C5.394 0 .012 5.38.012 12.015c0 2.122.551 4.195 1.597 6.012L.031 24l6.111-1.603c1.764.966 3.766 1.474 5.889 1.474 6.636 0 12.019-5.38 12.019-12.015S18.667 0 12.031 0zm0 21.84c-1.794 0-3.55-.482-5.086-1.395l-.365-.216-3.774.99.996-3.682-.237-.378a9.988 9.988 0 0 1-1.528-5.304C2.037 6.49 6.52 2.015 12.031 2.015c5.509 0 9.991 4.476 9.991 9.985 0 5.507-4.482 9.98-9.991 9.98zm5.485-7.498c-.302-.15-1.782-.878-2.059-.979-.277-.101-.479-.15-.681.151-.202.302-.781.979-.957 1.18-.176.201-.352.226-.654.075-1.285-.646-2.316-1.442-3.21-2.946-.201-.341-.019-.516.126-.665.132-.135.302-.351.453-.526.151-.176.201-.301.302-.502.101-.201.05-.377-.025-.527-.076-.151-.681-1.644-.932-2.247-.245-.589-.495-.509-.681-.518-.176-.008-.377-.01-.578-.01s-.529.075-.805.377c-.277.301-1.057 1.031-1.057 2.513s1.082 2.912 1.233 3.113c.151.2 2.122 3.242 5.142 4.545 2.188.941 3.02.825 3.522.696.581-.148 1.782-.729 2.033-1.433.252-.704.252-1.307.176-1.433-.075-.126-.277-.201-.578-.352z"/></svg>
                        </a>
                    </div>
                    <a href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>" class="btn-footer-denuncia"><span class="material-symbols-outlined" aria-hidden="true">report</span> Envía tu denuncia</a>
                </div>
            </div>
        </div><!-- .footer-widgets -->

        <div class="site-info container">
            <div class="site-info-inner">
                <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. Todos los derechos reservados.</p>
                <div class="footer-legal">
                    <a href="<?php echo esc_url( home_url( '/terminos-y-condiciones/' ) ); ?>">Términos y Condiciones</a>
                    <a href="<?php echo esc_url( home_url( '/politica-de-cookies/' ) ); ?>">Política de Cookies</a>
                    <a href="<?php echo esc_url( home_url( '/politica-de-privacidad/' ) ); ?>">Política de Privacidad</a>
                </div>
            </div>
        </div><!-- .site-info -->
    </footer><!-- #colophon -->
</div><!-- #page -->

<!-- Visor Lightbox Modal Interactivo de Portadas -->
<div id="portada-lightbox-modal" class="portada-modal" aria-hidden="true" role="dialog">
    <div class="portada-modal-backdrop"></div>
    
    <!-- Controles superiores e información -->
    <div class="portada-modal-bar">
        <h3 id="portada-modal-title">Visor de Portada</h3>
        <div class="portada-modal-actions">
            <!-- Botón de Descarga -->
            <a id="portada-download-btn" href="" download class="portada-modal-download" title="Descargar Portada" aria-label="Descargar Portada" style="color: white; margin-right: 15px; text-decoration: none; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.1); border-radius: 50%; width: 40px; height: 40px; transition: background 0.3s;">
                <span class="material-symbols-outlined" aria-hidden="true">download</span>
            </a>
            <!-- Botón de Cerrar -->
            <button class="portada-modal-close" aria-label="Cerrar visor" title="Cerrar">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </div>
    </div>

    <!-- Contenedor principal interactivo -->
    <div class="portada-modal-body">
        <div class="portada-viewport" id="portada-viewport">
            <div class="portada-image-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; gap: 20px; height: auto; max-width: 100%; position: relative;">
                <img id="portada-lightbox-image" src="" alt="Portada" draggable="false">
            </div>
        </div>
    </div>

    <!-- Controles flotantes inferiores de Zoom -->
    <div class="portada-zoom-controls">
        <button id="portada-zoom-out" class="zoom-btn" title="Alejar (Zoom -)" aria-label="Alejar (Zoom -)">
            <span class="material-symbols-outlined" aria-hidden="true">zoom_out</span>
        </button>
        <button id="portada-zoom-reset" class="zoom-btn text-btn" title="Restablecer" aria-label="Restablecer zoom al 100%">
            100%
        </button>
        <button id="portada-zoom-in" class="zoom-btn" title="Acercar (Zoom +)" aria-label="Acercar (Zoom +)">
            <span class="material-symbols-outlined" aria-hidden="true">zoom_in</span>
        </button>
    </div>
</div>

<!-- Swup JS -->
<script src="https://unpkg.com/swup@4"></script>
<script src="https://unpkg.com/@swup/scripts-plugin@2"></script>
<?php wp_footer(); ?>
<!-- Organization Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "NewsMediaOrganization",
  "name": <?php echo wp_json_encode( get_bloginfo( 'name' ) ); ?>,
  "url": <?php echo wp_json_encode( esc_url( home_url( '/' ) ) ); ?>,
  "logo": <?php echo wp_json_encode( has_custom_logo() ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : '' ); ?>
}
</script>
<!-- Reproductor Flotante Global (Fuera de Swup para que no se corte) -->
<div class="es-floating-radio" id="esFloatingRadio">
    <div class="es-floating-inner">
        <button class="es-float-btn" id="esFloatPlay" onclick="toggleFloatRadio()" aria-label="Reproducir Radio Vital 101.5 FM">
            <span class="material-symbols-outlined" aria-hidden="true" id="esFloatIcon">play_arrow</span>
        </button>
        <div class="es-float-info">
            <span class="es-float-title">Vital 101.5 FM</span>
            <span class="es-float-status" id="esFloatStatus">Haz clic para escuchar</span>
        </div>
        <div class="es-float-waves" id="esFloatWaves">
            <div class="es-float-bar" style="--d: 0s"></div>
            <div class="es-float-bar" style="--d: 0.2s"></div>
            <div class="es-float-bar" style="--d: 0.4s"></div>
            <div class="es-float-bar" style="--d: 0.1s"></div>
            <div class="es-float-bar" style="--d: 0.3s"></div>
        </div>
        <button class="es-float-close" onclick="closeFloatRadio()" title="Cerrar reproductor" aria-label="Cerrar reproductor">
            <span class="material-symbols-outlined" aria-hidden="true">close</span>
        </button>
    </div>
</div>
<audio id="esFloatAudio" preload="none"></audio>

</body>
</html>
