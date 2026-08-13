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
 * Template Name: Política de Cookies
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
                <h1 class="entry-title" style="font-size: 2.5rem; color: var(--color-text); font-weight: bold; margin: 0;">Política de Cookies</h1>
                <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-top: 10px;">Última actualización: 29 de abril de 2026</p>
            </header>

            <div class="entry-content terms-content" style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-muted); font-family: var(--font-ui);">
                <p>Esta Política de Cookies explica cómo el sitio web que está visitando (en adelante, "el Sitio Web" o "la Plataforma"), operado por la empresa titular del medio de comunicación (en adelante, "el Responsable del Tratamiento"), utiliza cookies y tecnologías similares. Al acceder y navegar por el Sitio Web, usted acepta el uso de cookies en los términos aquí descritos.</p>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">1. ¿Qué son las Cookies?</h2>
                <p>Las cookies son pequeños archivos de texto que los sitios web instalan en el dispositivo del usuario (ordenador, tableta, teléfono móvil) a través del navegador. Su finalidad es almacenar información sobre la visita, recordar preferencias, facilitar la navegación y ayudar a comprender cómo se utiliza el sitio. Las cookies no dañan el dispositivo y pueden ser gestionadas o desactivadas por el propio usuario.</p>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">2. Tipos de Cookies Utilizadas</h2>
                <p>En este Sitio Web utilizamos los siguientes tipos de cookies:</p>

                <h3 style="font-size: 1.4rem; color: var(--color-text); margin-top: 20px;">a) Cookies Técnicas o Necesarias</h3>
                <p>Son esenciales para el funcionamiento básico de la Plataforma, como la gestión de la sesión de usuario, la seguridad, el acceso a áreas restringidas o el equilibrio de carga. No recopilan información personal y son indispensables para la navegación. Su desactivación podría impedir el uso correcto del sitio.</p>

                <h3 style="font-size: 1.4rem; color: var(--color-text); margin-top: 20px;">b) Cookies de Preferencias o Personalización</h3>
                <p>Permiten recordar elecciones del usuario (por ejemplo, el idioma, la región o la configuración de accesibilidad) para ofrecer una experiencia más personalizada. Estas cookies no identifican personalmente al usuario a menos que esté registrado.</p>

                <h3 style="font-size: 1.4rem; color: var(--color-text); margin-top: 20px;">c) Cookies de Análisis o Rendimiento</h3>
                <p>Recogen información estadística anónima sobre el tráfico y el comportamiento de los usuarios en el sitio (páginas visitadas, tiempo de permanencia, origen de la visita, etc.). Se utilizan para medir y mejorar el rendimiento de la Plataforma. Para ello podemos emplear servicios de terceros como Google Analytics (que pueden instalar sus propias cookies).</p>

                <h3 style="font-size: 1.4rem; color: var(--color-text); margin-top: 20px;">d) Cookies de Publicidad y Redes Sociales</h3>
                <p>Algunas páginas pueden incluir contenido incrustado de redes sociales (vídeos de YouTube, botones para compartir, etc.) o mostrar publicidad gestionada por terceros. Estos servicios pueden instalar cookies para rastrear sus hábitos de navegación y mostrar anuncios personalizados. La Plataforma no tiene control directo sobre estas cookies, por lo que recomendamos revisar las políticas de privacidad de dichos terceros.</p>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">3. Cookies Concretas Empleadas</h2>
                <p>A continuación se detallan algunas de las cookies que pueden ser utilizadas (la lista exacta dependerá de la configuración del sistema y de las decisiones del Responsable del Tratamiento):</p>

                <div style="overflow-x: auto; margin-bottom: 20px;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; background: var(--color-bg-secondary); border-radius: 8px; overflow: hidden;">
                        <thead>
                            <tr>
                                <th style="border: 1px solid var(--color-border); padding: 12px; background: rgba(0,0,0,0.05); color: var(--color-text);">Nombre de la cookie</th>
                                <th style="border: 1px solid var(--color-border); padding: 12px; background: rgba(0,0,0,0.05); color: var(--color-text);">Tipo</th>
                                <th style="border: 1px solid var(--color-border); padding: 12px; background: rgba(0,0,0,0.05); color: var(--color-text);">Propósito</th>
                                <th style="border: 1px solid var(--color-border); padding: 12px; background: rgba(0,0,0,0.05); color: var(--color-text);">Duración</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="border: 1px solid var(--color-border); padding: 12px;"><code>wordpress_logged_in_*</code></td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Técnica</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Mantiene la sesión del usuario registrado</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Sesión</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid var(--color-border); padding: 12px;"><code>wordpress_test_cookie</code></td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Técnica</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Verifica si el navegador acepta cookies</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Sesión</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid var(--color-border); padding: 12px;"><code>wp-settings-*</code></td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Preferencias</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Guarda preferencias de visualización del usuario</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">1 año</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid var(--color-border); padding: 12px;"><code>_ga</code></td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Análisis</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Identificador de Google Analytics para distinguir usuarios</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">2 años</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid var(--color-border); padding: 12px;"><code>_gid</code></td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Análisis</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Usada por Google Analytics para distinguir usuarios</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">24 horas</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid var(--color-border); padding: 12px;"><code>cookie_notice_accepted</code></td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Preferencias</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Almacena si el usuario ha aceptado el aviso de cookies</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">1 mes</td>
                            </tr>
                            <tr>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Cookies de terceros</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Publicidad / Social</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Incorporadas por plugins de redes sociales o anunciantes</td>
                                <td style="border: 1px solid var(--color-border); padding: 12px;">Variable</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p>Las cookies de terceros están sujetas a las políticas propias de cada proveedor. El Responsable del Tratamiento no se hace responsable de las prácticas de estos terceros.</p>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">4. Consentimiento del Usuario</h2>
                <p>De conformidad con los artículos 57, 58 y 60 de la Constitución de la República Bolivariana de Venezuela (derecho a la información, libertad de expresión y protección al honor, la intimidad y la propia imagen), la Ley sobre Delitos Informáticos y el marco normativo de CONATEL para servicios de información, la instalación de cookies que impliquen el tratamiento de datos de navegación se fundamenta en el consentimiento libre, informado e inequívoco del usuario. Al acceder al Sitio Web, usted encontrará información y opciones sobre el uso de cookies, mediante las cuales podrá:</p>
                <ul style="margin-bottom: 20px; padding-left: 20px;">
                    <li style="margin-bottom: 10px;">Aceptar todas las cookies.</li>
                    <li style="margin-bottom: 10px;">Rechazar las cookies no esenciales.</li>
                    <li style="margin-bottom: 10px;">Configurar sus preferencias de navegación a través de su navegador.</li>
                </ul>
                <p>El mero hecho de continuar navegando después de haber sido informado, sin modificar la configuración de su navegador, será interpretado como una aceptación tácita del uso de cookies. No obstante, usted puede retirar su consentimiento en cualquier momento eliminando las cookies instaladas a través de los ajustes de su navegador.</p>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">5. Cómo Deshabilitar o Gestionar las Cookies</h2>
                <p>Usted tiene pleno control sobre las cookies. La mayoría de los navegadores permiten bloquear, eliminar o ser notificado antes de que una cookie sea instalada. A continuación le mostramos cómo acceder a la configuración de cookies en los navegadores más comunes:</p>
                <ul style="margin-bottom: 20px; padding-left: 20px;">
                    <li style="margin-bottom: 10px;"><strong>Google Chrome:</strong> Configuración → Privacidad y seguridad → Cookies y otros datos de sitios.</li>
                    <li style="margin-bottom: 10px;"><strong>Mozilla Firefox:</strong> Opciones → Privacidad y Seguridad → Cookies y datos del sitio.</li>
                    <li style="margin-bottom: 10px;"><strong>Microsoft Edge:</strong> Configuración → Cookies y permisos del sitio.</li>
                    <li style="margin-bottom: 10px;"><strong>Safari:</strong> Preferencias → Privacidad.</li>
                    <li style="margin-bottom: 10px;"><strong>Opera:</strong> Configuración → Privacidad y seguridad → Cookies.</li>
                </ul>
                <p>Tenga en cuenta que bloquear todas las cookies podría afectar la funcionalidad de la Plataforma y limitar su experiencia de usuario.</p>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">6. Transferencia de Datos a Terceros Países</h2>
                <p>Algunas cookies de terceros (por ejemplo, las de Google Analytics) pueden implicar la transferencia de datos a servidores ubicados fuera de la República Bolivariana de Venezuela. Al aceptar dichas cookies, usted consiente expresamente esta transferencia internacional de sus datos con fines exclusivamente estadísticos y de mejora del servicio, siempre en el marco de las garantías que ofrece la legislación aplicable.</p>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">7. Modificaciones de la Política de Cookies</h2>
                <p>El Responsable del Tratamiento se reserva el derecho a modificar esta Política de Cookies en cualquier momento. Cualquier cambio será publicado en esta misma página y, cuando sea legalmente exigible, se solicitará de nuevo el consentimiento del usuario. Le recomendamos revisar esta página periódicamente para mantenerse informado sobre cómo utilizamos las cookies.</p>

                <h2 style="font-size: 1.8rem; color: var(--color-text); margin-top: 30px; margin-bottom: 15px;">8. Legislación Aplicable</h2>
                <p>Esta Política de Cookies se rige e interpreta conforme a las leyes de la República Bolivariana de Venezuela, especialmente la Constitución (Art. 60 - Habeas Data y Privacidad), la Ley de Responsabilidad Social en Radio, Televisión y Medios Electrónicos (RESORTE-ME), la Ley sobre Delitos Informáticos y las regulaciones aplicables de CONATEL, sin perjuicio de los estándares técnicos internacionales para garantizar la transparencia y la privacidad del lector.</p>

                <div style="margin-top: 40px; padding: 20px; background: rgba(0,0,0,0.03); border-radius: 8px; text-align: center; border: 1px solid var(--color-border);">
                    <p style="margin: 0; font-weight: bold; color: var(--color-text);">Al navegar por este Sitio Web, usted reconoce haber sido informado sobre el uso de cookies y acepta las condiciones descritas en la presente política. Si tiene dudas sobre el tratamiento de sus datos, puede dirigirse a la sección de Política de Privacidad del sitio, donde encontrará información adicional sobre el responsable del tratamiento y el ejercicio de sus derechos ARCO (Acceso, Rectificación, Cancelación y Oposición).</p>
                </div>
            </div>
        </article>
    </div>
</main>

<?php
get_footer();
