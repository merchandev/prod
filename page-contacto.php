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
 * Template Name: Página de Contacto
 *
 * Plantilla para la página de contacto con formulario AJAX y diseño personalizado.
 *
 * @package Pro
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="contact-page-wrapper">
        <div class="container contact-container">
            
            <header class="contact-header">
                <h1 class="page-title"><?php the_title(); ?></h1>
                <?php if ( get_the_content() ) : ?>
                    <div class="contact-description">
                        <?php the_content(); ?>
                    </div>
                <?php endif; ?>
            </header>

            <div class="contact-form-container">
                <!-- Capa de Formulario -->
                <form id="pro-contact-form" class="pro-contact-form" novalidate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact-name">Nombre y apellido</label>
                            <input type="text" id="contact-name" name="name" placeholder="Ingrese nombre y apellido..." required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact-email">Correo electrónico</label>
                            <input type="email" id="contact-email" name="email" placeholder="Ingrese correo electrónico..." required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact-phone">Número de teléfono</label>
                            <input type="tel" id="contact-phone" name="phone" placeholder="Ingrese número de teléfono..." required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact-address">Dirección</label>
                            <input type="text" id="contact-address" name="address" placeholder="Ingrese su dirección ..." required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact-department">Selecciona un departamento</label>
                            <select id="contact-department" name="department" required>
                                <option value="" disabled selected>Seleccione...</option>
                                <option value="Prensa">Prensa</option>
                                <option value="Administración">Administración</option>
                                <option value="Clasificados y Carteles">Clasificados y Carteles</option>
                                <option value="Publicidad">Publicidad</option>
                                <option value="Denuncia">Denuncia</option>
                                <option value="Solicitudes">Solicitudes</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact-message">Mensaje</label>
                            <textarea id="contact-message" name="message" rows="5" placeholder="Escriba su mensaje aquí..." required></textarea>
                        </div>
                    </div>

                    <div class="form-row form-consent-row" style="margin-top: 15px; margin-bottom: 20px;">
                        <label class="consent-label" style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.9rem; cursor: pointer; color: var(--color-text);">
                            <input type="checkbox" id="contact-consent" name="consent" required style="margin-top: 3px;">
                            <span>He leído y acepto la <a href="<?php echo esc_url( home_url( '/politica-de-privacidad/' ) ); ?>" target="_blank" rel="noopener noreferrer" style="color: var(--color-primary); text-decoration: underline;">Política de Privacidad</a> y autorizo el tratamiento de mis datos personales para atender esta solicitud.</span>
                        </label>
                    </div>

                    <div class="form-submit">
                        <button type="submit" id="contact-submit-btn" class="button button-primary">
                            <span class="btn-text">Enviar Mensaje</span>
                            <span class="btn-spinner material-symbols-outlined" aria-hidden="true" style="display:none;">autorenew</span>
                        </button>
                    </div>
                    
                    <div id="contact-form-error" class="form-error-msg" style="display:none;"></div>
                </form>

                <!-- Capa de Éxito (Animación) -->
                <div id="contact-success-layer" class="contact-success-layer" style="display:none;">
                    <div class="success-animation">
                        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>
                    <h2>¡Gracias por contactarnos!</h2>
                    <p>Tu mensaje ha sido enviado exitosamente. Nos pondremos en contacto contigo a la brevedad posible.</p>
                </div>
            </div>

        </div>
    </div>
</main>

<?php
get_footer();
