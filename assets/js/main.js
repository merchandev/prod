/**
 * @author Arturo Merchan | Merchan.Dev | Espressivo Venezuela,C.A
 * 
 * ADVERTENCIA LEGAL:
 * Queda totalmente prohibida su reproduccion, edicion, venta, propaganda, alteracion 
 * o cualquier otra accion que de una u otra forma violente la propiedad intelectual, 
 * material y digital de este proyecto. Esta infraccion esta prohibida y penada por la ley.
 */
/**
 * Script principal de Pro
 * Maneja el modo oscuro, menú móvil, ticker, sliders, polling de noticias y scroll infinito.
 */

// =====================================================================
//  Diseñado y desarrollado por Merchan.Dev & Espressivo Venezuela, C.A
// =====================================================================
console.log(
    '%c Merchan.Dev %c & Espressivo Venezuela, C.A ',
    'background:#111827; color:#f59e0b; font-weight:bold; font-size:13px; padding:4px 8px; border-radius:4px 0 0 4px;',
    'background:#f59e0b; color:#111827; font-weight:bold; font-size:13px; padding:4px 8px; border-radius:0 4px 4px 0;'
);
console.log('%cDiseño y desarrollo web · https://merchan.dev', 'color:#6b7280; font-size:11px;');

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Modo Oscuro (Dark Mode)
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const currentTheme = localStorage.getItem('theme');
    
    if (currentTheme === 'dark') {
        document.body.classList.add('dark-mode');
    }

    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.toggle('dark-mode');
            let theme = 'light';
            if (document.body.classList.contains('dark-mode')) {
                theme = 'dark';
            }
            localStorage.setItem('theme', theme);
        });
    }

    // 2. Menú Móvil a Pantalla Completa y Submenús
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.main-navigation');
    
    if (menuToggle && navMenu) {
        // Toggle principal
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            navMenu.classList.toggle('toggled');
            const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', !isExpanded);
            
            // Bloquear scroll de fondo si está abierto
            if (navMenu.classList.contains('toggled')) {
                menuToggle.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true" style="vertical-align: middle;">close</span>';
                document.body.style.overflow = 'hidden';
            } else {
                menuToggle.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true" style="vertical-align: middle;">menu</span>';
                document.body.style.overflow = '';
            }
        });

        // Lógica de Submenús (Eliminada para que los enlaces padre funcionen y todo esté expandido permanentemente)
    }

    // 3. News Ticker Slider
    const tickerSlider = document.getElementById('newsTickerSlider');
    if (tickerSlider) {
        const slides = Array.from(tickerSlider.querySelectorAll('.ticker-slide'));
        if (slides.length > 0) {
            // Asegurarse de que solo el primero tiene la clase active al arrancar
            slides.forEach((s, i) => {
                s.classList.remove('active', 'prev');
                if (i === 0) s.classList.add('active');
            });

            if (slides.length > 1) {
                let current = 0;
                let isAnimating = false;

                setInterval(() => {
                    if (isAnimating) return;
                    isAnimating = true;

                    const prevIndex = current;
                    current = (current + 1) % slides.length;

                    // Salida: el slide actual pasa a .prev
                    slides[prevIndex].classList.remove('active');
                    slides[prevIndex].classList.add('prev');

                    // Entrada: el siguiente aparece con .active
                    slides[current].classList.add('active');

                    // Limpiar .prev cuando termina la transición CSS (450ms)
                    setTimeout(() => {
                        slides[prevIndex].classList.remove('prev');
                        isAnimating = false;
                    }, 500);

                }, 5000);
            }
        }
    }


    // 4. Paginación AJAX "Cargar Más" (Botón para Inicio)
    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn && typeof pro_loadmore_params !== 'undefined') {
        let currentPage = parseInt(pro_loadmore_params.current_page);
        const maxPage = parseInt(pro_loadmore_params.max_page);

        if (currentPage >= maxPage) {
            loadMoreBtn.style.display = 'none';
        }

        loadMoreBtn.addEventListener('click', function() {
            loadMoreBtn.textContent = 'Cargando...';
            loadMoreBtn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'pro_load_more_posts');
            formData.append('category_id', pro_loadmore_params.category_id);
            formData.append('page', currentPage);
            formData.append('nonce', pro_loadmore_params.nonce);

            fetch(pro_loadmore_params.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                if (html.trim() !== '') {
                    // Seleccionar el contenedor (ej. portada)
                    let grid = document.querySelector('.secondary-posts-grid') || document.querySelector('.category-grid');
                    if (grid) {
                        grid.insertAdjacentHTML('beforeend', html);
                    }
                    
                    currentPage++;
                    if (currentPage >= maxPage) {
                        loadMoreBtn.style.display = 'none';
                    } else {
                        loadMoreBtn.textContent = 'Cargar más';
                        loadMoreBtn.disabled = false;
                    }
                } else {
                    loadMoreBtn.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error cargando posts:', error);
                loadMoreBtn.textContent = 'Cargar más';
                loadMoreBtn.disabled = false;
            });
        });
    }

    // 5. Barra de progreso de lectura (solo en Single)
    const progressBar = document.getElementById('reading-progress-bar');
    if (progressBar) {
        window.addEventListener('scroll', () => {
            const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
            const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const progress = (scrollTop / scrollHeight) * 100;
            progressBar.style.width = progress + '%';
        });
    }

    // 6. Actualización Automática de Noticias (AJAX Polling)
    function mostrarNotificacionNuevasNoticias() {
        if (!document.getElementById('new-posts-toast')) {
            const toast = document.createElement('div');
            toast.id = 'new-posts-toast';
            toast.className = 'new-posts-toast';
            toast.title = 'Hay nuevas noticias. Haz clic para actualizar.';
            toast.innerHTML = '';
            
            toast.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            });

            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
        }
    }

    if (typeof pro_loadmore_params !== 'undefined' && pro_loadmore_params.latest_date) {
        setInterval(function() {
            const formData = new FormData();
            formData.append('action', 'pro_check_new_posts');
            formData.append('latest_date', pro_loadmore_params.latest_date);
            formData.append('nonce', pro_loadmore_params.nonce);

            fetch(pro_loadmore_params.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.has_new_posts) {
                    mostrarNotificacionNuevasNoticias();
                }
            })
            .catch(error => console.error('Error comprobando nuevas noticias:', error));
        }, 60000);
    }

    // 7. Ad Sliders (Publicidad In-Feed)
    function initAdSliders() {
        const sliders = document.querySelectorAll('.in-feed-ad-slider, .header-ad');
        sliders.forEach(slider => {
            const slides = slider.querySelectorAll('.ad-slide');
            if (slides.length <= 1) return;

            let currentIndex = 0;
            setInterval(() => {
                slides[currentIndex].classList.remove('active');
                currentIndex = (currentIndex + 1) % slides.length;
                slides[currentIndex].classList.add('active');
            }, 5000);
        });
    }
    initAdSliders();

    // 8. Scroll Infinito en Categorías
    const initInfiniteScroll = () => {
        let trigger = document.querySelector('.infinite-scroll-trigger');
        if (!trigger || typeof pro_loadmore_params === 'undefined') return;

        let isFetching = false;

        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !isFetching) {
                let currentPage = parseInt(trigger.getAttribute('data-current-page'));
                let maxPages = parseInt(trigger.getAttribute('data-max-pages'));
                let catId = trigger.getAttribute('data-cat-id');

                if (currentPage < maxPages) {
                    isFetching = true;
                    fetchNextPageAJAX(currentPage, catId, maxPages);
                }
            }
        }, {
            rootMargin: '200px'
        });

        observer.observe(trigger);

        function fetchNextPageAJAX(currentPage, catId, maxPages) {
            const formData = new FormData();
            formData.append('action', 'pro_load_more_posts');
            formData.append('category_id', catId);
            formData.append('page', currentPage);
            formData.append('nonce', pro_loadmore_params.nonce);

            fetch(pro_loadmore_params.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                if (html.trim() !== '') {
                    const grid = document.querySelector('.category-grid');
                    if (grid) {
                        grid.insertAdjacentHTML('beforeend', html);
                    }
                    
                    let nextPage = currentPage + 1;
                    trigger.setAttribute('data-current-page', nextPage);
                    
                    if (nextPage >= maxPages) {
                        trigger.remove();
                        observer.disconnect();
                    } else {
                        isFetching = false;
                    }
                } else {
                    trigger.remove();
                    observer.disconnect();
                }
            })
            .catch(error => {
                console.error('Error cargando noticias:', error);
                isFetching = false;
            });
        }
    };
    
    initInfiniteScroll();

    // ==========================================
    // VISOR PDF DE CARTELES (LIGHTBOX)
    // ==========================================
    const carteles = document.querySelectorAll('.card-cartel');
    const pdfModal = document.getElementById('pdf-lightbox-modal');
    
    if (carteles.length > 0 && pdfModal) {
        const modalIframe = document.getElementById('pdf-iframe');
        const modalTitle = document.getElementById('pdf-modal-title');
        const closeBtn = document.querySelector('.pdf-modal-close');

        // Abrir Modal
        carteles.forEach(cartel => {
            cartel.addEventListener('click', function(e) {
                e.preventDefault();
                const pdfUrl = this.getAttribute('data-pdf-url');
                const title = this.querySelector('.entry-title').innerText;

                if (pdfUrl) {
                    modalTitle.innerText = title;
                    modalIframe.src = pdfUrl;
                    pdfModal.classList.add('active');
                    document.body.style.overflow = 'hidden'; // Prevenir scroll de fondo
                } else {
                    alert('Este cartel no tiene un documento PDF adjunto.');
                }
            });
        });

        // Función para cerrar modal
        const closeModal = () => {
            pdfModal.classList.remove('active');
            document.body.style.overflow = '';
            setTimeout(() => {
                modalIframe.src = ''; // Limpiar iframe después de la animación
            }, 300);
        };

        // Cerrar con botón X
        closeBtn.addEventListener('click', closeModal);

        // Cerrar al hacer clic fuera del contenido
        pdfModal.addEventListener('click', function(e) {
            if (e.target === pdfModal) {
                closeModal();
            }
        });

        // Cerrar con la tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && pdfModal.classList.contains('active')) {
                closeModal();
            }
        });
    }

    // ==========================================
    // FORMULARIO DE CONTACTO (AJAX)
    // ==========================================
    const contactForm = document.getElementById('pro-contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Limpiar errores previos
            const errorLayer = document.getElementById('contact-form-error');
            errorLayer.style.display = 'none';
            errorLayer.innerText = '';
            
            const inputs = contactForm.querySelectorAll('input, select, textarea');
            let hasError = false;

            inputs.forEach(input => {
                input.parentElement.classList.remove('has-error');
                if (input.required && !input.value.trim()) {
                    hasError = true;
                    input.parentElement.classList.add('has-error');
                }
                if (input.type === 'email' && input.value.trim() !== '') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(input.value.trim())) {
                        hasError = true;
                        input.parentElement.classList.add('has-error');
                    }
                }
            });

            if (hasError) {
                errorLayer.innerText = 'Por favor, completa correctamente todos los campos marcados en rojo.';
                errorLayer.style.display = 'block';
                return;
            }

            // Preparar datos
            const formData = new FormData(contactForm);
            formData.append('action', 'pro_submit_contact_form');
            formData.append('nonce', typeof pro_ajax !== 'undefined' ? pro_ajax.nonce : ''); // Usar nonce si está disp

            // UI Loading
            const btn = document.getElementById('contact-submit-btn');
            const btnText = btn.querySelector('.btn-text');
            const btnSpinner = btn.querySelector('.btn-spinner');
            
            btn.disabled = true;
            btnText.style.display = 'none';
            btnSpinner.style.display = 'inline-block';

            // Fetch AJAX
            const ajaxUrl = (typeof pro_ajax !== 'undefined') ? pro_ajax.ajax_url : (typeof pro_loadmore_params !== 'undefined' ? pro_loadmore_params.ajax_url : '/wp-admin/admin-ajax.php');

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btnText.style.display = 'inline-block';
                btnSpinner.style.display = 'none';

                if (data.success) {
                    // Ocultar formulario suavemente y mostrar éxito
                    contactForm.style.transition = 'opacity 0.3s ease';
                    contactForm.style.opacity = '0';
                    setTimeout(() => {
                        contactForm.style.display = 'none';
                        const successLayer = document.getElementById('contact-success-layer');
                        successLayer.style.display = 'block';
                    }, 300);
                } else {
                    errorLayer.innerText = data.data.message || 'Error desconocido al enviar.';
                    errorLayer.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.disabled = false;
                btnText.style.display = 'inline-block';
                btnSpinner.style.display = 'none';
                errorLayer.innerText = 'Ocurrió un error de conexión. Intenta nuevamente.';
                errorLayer.style.display = 'block';
            });
        });

        // Limpiar error al escribir
        contactForm.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('input', function() {
                this.parentElement.classList.remove('has-error');
            });
        });
    }

    /* ==========================================================================
       VALIDACIÓN DE FORMULARIOS DE BÚSQUEDA
       ========================================================================== */
    const searchForms = document.querySelectorAll('form[role="search"], .search-form');
    
    searchForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const input = form.querySelector('input[name="s"]');
            if (!input) return;
            
            let val = input.value.trim();
            
            // 1. Vacío o muy corto
            if (val.length < 2) {
                e.preventDefault();
                input.focus();
                // Opcional: mostrar un mensaje visual pequeño
                input.style.border = '2px solid #ef4444';
                setTimeout(() => input.style.border = '', 2000);
                return;
            }
            
            // 2. Contiene enlaces o dominios
            const urlPattern = /(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?|www\.|[a-zA-Z0-9\-\.]+\.(com|net|org|info)/i;
            if (urlPattern.test(val)) {
                e.preventDefault();
                input.value = '';
                input.placeholder = 'Enlaces no permitidos';
                input.style.border = '2px solid #ef4444';
                setTimeout(() => {
                    input.placeholder = 'Buscar noticias...';
                    input.style.border = '';
                }, 3000);
                return;
            }
            
            // 3. Contiene solo números o caracteres extraños (permitir letras, espacios, acentos, y puntuación básica de texto)
            // Rechaza si hay etiquetas HTML, corchetes, o demasiados signos
            const strictPattern = /^[a-zA-ZñÑáéíóúÃÉÃÓÚüÃœ\s\.,\-\¿\?¡!]+$/;
            
            // Si el valor NO coincide con el patrón estricto
            if (!strictPattern.test(val)) {
                e.preventDefault();
                input.value = '';
                input.placeholder = 'Texto inválido (evita números/símbolos)';
                input.style.border = '2px solid #ef4444';
                setTimeout(() => {
                    input.placeholder = 'Buscar noticias...';
                    input.style.border = '';
                }, 3000);
                return;
            }
        });
    });

    // ==========================================
    // LIGHTBOX INTERACTIVO DE PORTADAS (ZOOM & PAN)
    // ==========================================
    const portadas = document.querySelectorAll('.card-portada');
    const portadaModal = document.getElementById('portada-lightbox-modal');
    
    if (portadas.length > 0 && portadaModal) {
        const modalImage = document.getElementById('portada-lightbox-image');
        const modalTitle = document.getElementById('portada-modal-title');
        const downloadBtn = document.getElementById('portada-download-btn');
        const closeBtn = document.querySelector('.portada-modal-close');
        const backdrop = document.querySelector('.portada-modal-backdrop');
        
        // Botones de Zoom
        const zoomInBtn = document.getElementById('portada-zoom-in');
        const zoomOutBtn = document.getElementById('portada-zoom-out');
        const zoomResetBtn = document.getElementById('portada-zoom-reset');
        
        // Variables de Estado de Zoom y Arrastre (Pan)
        let zoomScale = 1;
        let isDragging = false;
        let startX = 0, startY = 0;
        let translateX = 0, translateY = 0;
        
        const zoomStep = 0.25;
        const maxZoom = 4;
        const minZoom = 0.5;
        
        // Función para aplicar transformaciones CSS de forma unificada
        const applyTransform = () => {
            modalImage.style.transform = `translate(${translateX}px, ${translateY}px) scale(${zoomScale})`;
            zoomResetBtn.innerText = Math.round(zoomScale * 100) + '%';
        };
        
        // Función para abrir el Lightbox
        portadas.forEach(portada => {
            portada.addEventListener('click', function(e) {
                // Si el clic proviene de un elemento de descarga, permitir comportamiento nativo
                if (e.target.closest('a[download]')) {
                    return;
                }
                
                e.preventDefault();
                const fullUrl = this.getAttribute('data-full-url');
                const titleElement = this.querySelector('.entry-title') || this.querySelector('.edition-title');
                const title = titleElement ? titleElement.innerText : 'Visor de Portada';
                
                if (fullUrl) {
                    modalTitle.innerText = title;
                    modalImage.src = fullUrl;
                    downloadBtn.href = fullUrl;
                    
                    // Resetear estado
                    zoomScale = 1;
                    translateX = 0;
                    translateY = 0;
                    applyTransform();
                    
                    portadaModal.classList.add('active');
                    portadaModal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden'; // Previene scroll de fondo
                    document.documentElement.style.overflow = 'hidden'; // Asegura que se oculte la barra principal
                    
                    // Forzar scroll al inicio de la imagen
                    const viewport = document.getElementById('portada-viewport');
                    if (viewport) viewport.scrollTop = 0;
                }
            });
        });
        
        // Función para cerrar el Lightbox
        const closePortadaModal = () => {
            portadaModal.classList.remove('active');
            portadaModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            document.documentElement.style.overflow = '';
            
            setTimeout(() => {
                modalImage.src = ''; // Limpiar src para liberar memoria
            }, 300);
        };
        
        // Cerrar al hacer clic en cualquier parte de la caja de luz, "tipo app"
        portadaModal.addEventListener('click', function(e) {
            // No cerrar si se hace clic en el botón de descarga o en los controles de zoom
            if (e.target.closest('#portada-download-btn') || e.target.closest('.portada-zoom-controls')) {
                return;
            }
            
            // No cerrar si hacen clic sobre la imagen (para permitir interacción, zoom y arrastre nativo)
            if (e.target.closest('#portada-lightbox-image')) {
                return;
            }
            
            closePortadaModal();
        });
        
        // Cerrar con Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && portadaModal.classList.contains('active')) {
                closePortadaModal();
            }
        });
        
        // --- CONTROL DE ZOOM ---
        
        // Acercar
        zoomInBtn.addEventListener('click', () => {
            if (zoomScale < maxZoom) {
                zoomScale += zoomStep;
                applyTransform();
            }
        });
        
        // Alejar
        zoomOutBtn.addEventListener('click', () => {
            if (zoomScale > minZoom) {
                zoomScale -= zoomStep;
                // Si vuelve a la escala normal o inferior, centrar imagen automáticamente
                if (zoomScale <= 1) {
                    translateX = 0;
                    translateY = 0;
                }
                applyTransform();
            }
        });
        
        // Restablecer Zoom
        zoomResetBtn.addEventListener('click', () => {
            zoomScale = 1;
            translateX = 0;
            translateY = 0;
            applyTransform();
        });
        
        // Zoom con la rueda del ratón (Mousewheel)
        portadaModal.addEventListener('wheel', (e) => {
            if (!portadaModal.classList.contains('active')) return;
            e.preventDefault();
            
            const delta = e.deltaY < 0 ? 1 : -1;
            
            if (delta === 1 && zoomScale < maxZoom) {
                zoomScale += zoomStep;
            } else if (delta === -1 && zoomScale > minZoom) {
                zoomScale -= zoomStep;
                if (zoomScale <= 1) {
                    translateX = 0;
                    translateY = 0;
                }
            }
            applyTransform();
        }, { passive: false });
        
        // --- CONTROL DE PANNING / DRAG (ARRASTRE) ---
        
        // Evento mouse down
        modalImage.addEventListener('mousedown', (e) => {
            if (zoomScale >= 1) { // Permitir arrastre siempre
                isDragging = true;
                modalImage.classList.add('panning');
                
                // Calcular posición inicial considerando la traslación actual
                startX = e.clientX - translateX;
                startY = e.clientY - translateY;
                
                e.preventDefault();
            }
        });
        
        // Evento mouse move (en document por si sale del viewport)
        document.addEventListener('mousemove', (e) => {
            if (isDragging) {
                translateX = e.clientX - startX;
                translateY = e.clientY - startY;
                applyTransform();
            }
        });
        
        // Evento mouse up
        document.addEventListener('mouseup', () => {
            isDragging = false;
            modalImage.classList.remove('panning');
        });
        
        // --- SOPORTE TACTIL (MOVILES) ---
        modalImage.addEventListener('touchstart', (e) => {
            if (zoomScale >= 1 && e.touches.length === 1) {
                isDragging = true;
                startX = e.touches[0].clientX - translateX;
                startY = e.touches[0].clientY - translateY;
            }
        }, { passive: true });
        
        document.addEventListener('touchmove', (e) => {
            if (isDragging && e.touches.length === 1) {
                translateX = e.touches[0].clientX - startX;
                translateY = e.touches[0].clientY - startY;
                applyTransform();
            }
        }, { passive: true });
        
        document.addEventListener('touchend', () => {
            isDragging = false;
        });
    }

    

    // ==========================================
    // BUSCADOR COLAPSABLE EN TABLET Y MÓVIL
    // ==========================================
    const topSearchForm = document.querySelector('.header-actions .search-form');
    const topSearchSubmit = document.querySelector('.header-actions .search-submit');
    const topSearchInput = document.querySelector('.header-actions .search-field');
    
    if (topSearchForm && topSearchSubmit && topSearchInput) {
        topSearchSubmit.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024) {
                if (!topSearchForm.classList.contains('is-expanded')) {
                    e.preventDefault(); 
                    topSearchForm.classList.add('is-expanded');
                    topSearchInput.focus();
                } else {
                    if (topSearchInput.value.trim() === '') {
                        e.preventDefault();
                        topSearchForm.classList.remove('is-expanded');
                    }
                }
            }
        });
        
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024 && topSearchForm.classList.contains('is-expanded')) {
                if (!topSearchForm.contains(e.target)) {
                    topSearchForm.classList.remove('is-expanded');
                }
            }
        });
    }

});


/* ==========================================================================
   E-SERVER RADIO PLAYER LOGIC
   ========================================================================== */
document.addEventListener('DOMContentLoaded', function() {
    const audio = document.getElementById('esRespAudio');
    if (!audio) return; // Only run if player exists on the current page

    const STREAM = 'https://radio.diarioeloriental.com/radio.aac';
    let playing = false;

    const btn    = document.getElementById('esRespPlay');
    const icon   = document.getElementById('esRespIcon');
    const status = document.getElementById('esRespStatus');
    const fill   = document.getElementById('esRespFill');
    const bar    = document.getElementById('esRespBar');
    const vol    = document.getElementById('esRespVol');
    const mIcon  = document.getElementById('esRespMuteIcon');

    window.toggleRespRadio = function() {
        playing ? stop() : start();
    };

    function start() {
        audio.src = STREAM + '?t=' + Date.now();
        status.innerText = 'CONECTANDO...';
        audio.play().then(() => {
            playing = true;
            bar.classList.add('es-playing');
            icon.innerText = 'pause';
            status.innerText = 'REPRODUCIENDO';
            animateFill();
        }).catch(() => {
            status.innerText = 'ERROR AL CONECTAR';
        });
    }

    function stop() {
        audio.pause(); audio.src = '';
        playing = false;
        bar.classList.remove('es-playing');
        icon.innerText = 'play_arrow';
        status.innerText = 'DETENIDO';
        fill.style.width = '0%';
    }

    function animateFill() {
        if (!playing) return;
        let p = 0;
        const interval = setInterval(() => {
            if (!playing) { clearInterval(interval); return; }
            p = (p + 0.1) % 100;
            fill.style.width = p + '%';
        }, 100);
    }

    window.updateRespVol = function() {
        audio.volume = vol.value;
        mIcon.innerText = vol.value == 0 ? 'volume_off' : 'volume_up';
    };

    window.toggleRespMute = function() {
        if (audio.volume > 0) {
            audio.v = audio.volume; audio.volume = 0; vol.value = 0;
            mIcon.innerText = 'volume_off';
        } else {
            audio.volume = audio.v || 1; vol.value = audio.volume;
            mIcon.innerText = 'volume_up';
        }
    };
});

/* ==========================================================================
   GLOBAL SWUP & FLOATING RADIO LOGIC
   ========================================================================== */
document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize Swup
    if (typeof Swup !== 'undefined' && typeof SwupScriptsPlugin !== 'undefined') {
        const swup = new Swup({
            plugins: [new SwupScriptsPlugin({
                head: true,
                body: true
            })]
        });
        
        // Swup Page View Event - Re-trigger anything necessary
        swup.hooks.on('page:view', () => {
            // Document listeners survive, but if there's anything else needed, it runs here.
            console.log('Swup navigated to new page');
        });
    }

    // 2. Floating Radio Logic
    const floatAudio = document.getElementById('esFloatAudio');
    const floatBtn = document.getElementById('esFloatPlay');
    const floatIcon = document.getElementById('esFloatIcon');
    const floatStatus = document.getElementById('esFloatStatus');
    const floatContainer = document.getElementById('esFloatingRadio');
    
    if (!floatAudio || !floatContainer) return;

    const STREAM = 'https://radio.diarioeloriental.com/radio.aac';
    let isFloatPlaying = false;

    window.toggleFloatRadio = function() {
        if (isFloatPlaying) {
            floatAudio.pause();
            floatAudio.src = '';
            isFloatPlaying = false;
            floatContainer.classList.remove('playing');
            floatIcon.innerText = 'play_arrow';
            floatStatus.innerText = 'DETENIDO';
            
            // Sync with dedicated page player if exists
            const pageIcon = document.getElementById('esRespIcon');
            if (pageIcon) {
                document.getElementById('esRespBar').classList.remove('es-playing');
                pageIcon.innerText = 'play_arrow';
                document.getElementById('esRespStatus').innerText = 'DETENIDO';
            }
        } else {
            floatAudio.src = STREAM + '?t=' + Date.now();
            floatStatus.innerText = 'CONECTANDO...';
            floatAudio.play().then(() => {
                isFloatPlaying = true;
                floatContainer.classList.add('playing');
                floatIcon.innerText = 'pause';
                floatStatus.innerText = 'EN VIVO';
                
                // Sync with dedicated page player if exists
                const pageIcon = document.getElementById('esRespIcon');
                if (pageIcon) {
                    document.getElementById('esRespBar').classList.add('es-playing');
                    pageIcon.innerText = 'pause';
                    document.getElementById('esRespStatus').innerText = 'REPRODUCIENDO';
                }
            }).catch(() => {
                floatStatus.innerText = 'ERROR';
            });
        }
    };

    window.closeFloatRadio = function() {
        if (isFloatPlaying) {
            window.toggleFloatRadio();
        }
        floatContainer.classList.add('hidden-by-user');
    };

    // Show floating player automatically after 3 seconds
    setTimeout(() => {
        if (!floatContainer.classList.contains('hidden-by-user')) {
            floatContainer.classList.add('show');
        }
    }, 3000);
});
