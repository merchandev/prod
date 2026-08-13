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
 * Template Name: Política de Privacidad
 * Template Post Type: page
 *
 * @package pro
 */

get_header();
if ( have_posts() ) { while ( have_posts() ) { the_post(); } }
?>

<main id="primary" class="site-main">
    <div class="container" style="max-width: 900px; padding: 40px 20px;">
        <article>
            <header class="page-header" style="margin-bottom: 30px; border-bottom: 2px solid var(--color-border); padding-bottom: 20px;">
                <h1 class="entry-title" style="font-size: 2.5rem; color: var(--color-text); font-weight: bold; margin: 0;">Política de Privacidad y Protección de Datos</h1>
                <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-top: 10px;">Última actualización: 23 de julio de 2026</p>
            </header>

            <div class="entry-content terms-content" style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-muted); font-family: var(--font-ui);">
                <p>En <strong>Espressivo Editorial / Diario El Oriental</strong> (en adelante, "el Medio", "la Plataforma" o "el Responsable del Tratamiento"), valoramos la confianza de nuestros lectores, anunciantes y usuarios. La presente Política de Privacidad describe de manera transparente qué datos personales recopilamos, con qué finalidades los tratamos, cómo los protegemos y cuáles son sus derechos como titular de la información.</p>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">1. Identificación del Responsable del Tratamiento</h2>
                <p>El responsable del tratamiento de los datos personales recabados en este sitio web es la empresa editora y titular del diario <strong>Diario El Oriental</strong>, con sede operativa en Maturín, Estado Monagas, República Bolivariana de Venezuela.</p>
                <ul>
                    <li><strong>Sitio Web:</strong> <?php echo esc_url( home_url( '/' ) ); ?></li>
                    <li><strong>Correo de Contacto y Privacidad:</strong> contacto@diarioeloriental.com</li>
                    <li><strong>Canal de Atención:</strong> Formulario de Contacto en línea.</li>
                </ul>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">2. Datos Personales que Recopilamos</h2>
                <p>Recopilamos únicamente los datos necesarios para brindar los servicios solicitados por el usuario:</p>
                <h3 style="font-size: 1.4rem; color: var(--color-text); margin-top: 20px;">a) Datos proporcionados voluntariamente por el usuario</h3>
                <p>A través de nuestros formularios de contacto, denuncias ciudadanas, solicitudes institucionales, contratación de anuncios o envío de carteles y edictos, recopilamos:</p>
                <ul>
                    <li>Nombre y apellido.</li>
                    <li>Dirección de correo electrónico.</li>
                    <li>Número de teléfono (fijo o móvil).</li>
                    <li>Dirección de contacto o ubicación geográfica de la denuncia/solicitud.</li>
                    <li>Departamento de destino (Prensa, Administración, Publicidad, Denuncias, Carteles).</li>
                    <li>Contenido del mensaje, adjuntos o documentación remitida.</li>
                </ul>

                <h3 style="font-size: 1.4rem; color: var(--color-text); margin-top: 20px;">b) Datos recopilados automáticamente (Navegación)</h3>
                <p>Al navegar por el portal, recopilamos datos técnicos anónimos o seudónimos como dirección IP, tipo de navegador, sistema operativo, páginas visitadas, fecha y hora de acceso, a través de herramientas de analítica web como Google Analytics y Google Search Console. Para más información, consulte nuestra <a href="<?php echo esc_url( home_url( '/politica-de-cookies/' ) ); ?>" style="color: var(--color-primary);">Política de Cookies</a>.</p>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">3. Finalidad y Base Legal del Tratamiento</h2>
                <p>Tratamos sus datos personales con las siguientes finalidades específicas:</p>
                <ol>
                    <li><strong>Atención de consultas y solicitudes:</strong> Responder a los mensajes enviados a través de nuestro portal.</li>
                    <li><strong>Recepción y validación de denuncias comunitarias:</strong> Verificar y canalizar denuncias enviadas al equipo de Redacción o Prensa.</li>
                    <li><strong>Gestión de publicidad y anuncios:</strong> Coordinar pautas publicitarias con anunciantes y marcas.</li>
                    <li><strong>Procesamiento de Carteles y Edictos:</strong> Verificar y publicar avisos legales conforme a la normativa correspondiente.</li>
                    <li><strong>Seguridad del portal:</strong> Detectar y prevenir accesos no autorizados, spam o actividades maliciosas.</li>
                </ol>
                <p>La base legal para el tratamiento de sus datos es el <strong>consentimiento libre, informado e inequívoco</strong> expresado mediante la casilla de aceptación obligatoria antes del envío de cualquier formulario.</p>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">4. Conservación y Supresión de Datos</h2>
                <p>Los datos personales se conservarán únicamente durante el tiempo estrictamente necesario para cumplir con la finalidad para la cual fueron recopilados, o según lo exijan los lapsos de prescripción legal vigentes. Tras este periodo, los datos serán eliminados o anonimizados de forma segura.</p>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">5. Medidas de Seguridad</h2>
                <p>Aplicamos medidas técnicas y organizativas de seguridad avanzadas para proteger sus datos personales contra acceso no autorizado, pérdida, alteración o divulgación:</p>
                <ul>
                    <li>Cifrado de la comunicación sitio-servidor mediante protocolo seguro HTTPS/TLS.</li>
                    <li>Acceso a la información restringido exclusivamente al personal autorizado según el rol (Dirección, Redacción, Administración).</li>
                    <li>Servidores protegidos con cortafuegos y políticas de copia de seguridad periódicas.</li>
                </ul>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">6. Derechos de los Usuarios</h2>
                <p>Como titular de los datos personales, usted tiene derecho a:</p>
                <ul>
                    <li><strong>Acceso:</strong> Solicitar información sobre qué datos personales suyos conservamos.</li>
                    <li><strong>Rectificación:</strong> Solicitar la corrección de datos inexactos o desactualizados.</li>
                    <li><strong>Cancelación / Supresión:</strong> Solicitar la eliminación de sus datos de nuestros archivos.</li>
                    <li><strong>Oposición:</strong> Oponerse al tratamiento de sus datos para fines específicos.</li>
                </ul>
                <p>Para ejercer cualquiera de estos derechos, puede enviar una comunicación escrita a nuestro correo de contacto o realizar la solicitud a través del <a href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>" style="color: var(--color-primary);">Formulario de Contacto</a> indicando en el asunto "Ejercicio de Derechos de Privacidad".</p>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">7. Modificaciones a la Política de Privacidad</h2>
                <p>Nos reservamos el derecho de actualizar o modificar esta Política de Privacidad en cualquier momento para adaptarla a novedades legislativas, jurisprudenciales o prácticas editoriales. La fecha de la última versión se indicará en el encabezado de esta página.</p>
            </div>
        </article>
    </div>
</main>

<?php
get_footer();
