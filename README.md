# Espressivo Theme — Framework Editorial SaaS & Marca Blanca

Este es un tema y framework de WordPress diseñado y desarrollado a medida para portales de noticias modernos, rápidos, profesionales y de alto rendimiento. Construido desde cero bajo una arquitectura de marca blanca (white-label) y SaaS, está optimizado para su fácil duplicación, reventa y distribución a editoriales y medios de comunicación digitales. Evita el uso excesivo de plugins de terceros y ofrece una experiencia de usuario (UX) premium de alto impacto comercial.

---

## 🚀 Características Principales

### 1. Motor SSIVO-SEO Nativo (Nuevo)
- **Arquitectura Híbrida Desacoplada:** El análisis de legibilidad ocurre 100% en el navegador usando React y `@wordpress/data`, con cero peticiones al servidor mientras el autor escribe.
- **Tabla Indexable Autónoma:** Los datos generados se guardan de forma atómica (UPSERT) en una tabla relacional propia (`ssivo_seo_indexable`), evitando la sobrecarga de `wp_postmeta`.
- **Inyección Ultra-Rápida:** El frontend anula el `<title>` nativo y realiza una única consulta directa por `post_id` inyectando `<title>` y `<meta name="description">` de forma casi instantánea en el `wp_head`.
- **Automatización Fallback:** Generación automática de meta descripción basada en RegEx al publicar la entrada, y asignación de imagen destacada automática si el autor olvida configurarla.

### 2. Sistema de "Carteles y Edictos" (PDF Lightbox)
- **Custom Post Type Dedicado:** Sección exclusiva en el panel de administración separada de las noticias.
- **Cuadrícula de Documentos:** Diseño vertical optimizado para miniaturas de documentos legales (`/carteles/`).
- **Visor PDF Nativo:** Al hacer clic en un cartel, se abre un visor integrado interactivo.

### 3. Formulario de Contacto Inteligente y Exportación
- **100% Nativo (Sin Contact Form 7):** Formulario HTML/JS procesado por AJAX sin recargar la página.
- **Base de Datos Propia:** Mensajes guardados en un CPT oculto con exportación a Excel/CSV.

### 4. Sistema de Gestión de Publicidad (Ad Manager)
- Soporte nativo para Banners en Header, In-Feed (entre noticias) y Sidebar, gestionados mediante CPT oculto.

### 5. Experiencia de Usuario y Diseño
- **Scroll Infinito Automático (AJAX).**
- **Grid "Hero" Dinámico** y **Menús Inteligentes.**

### 6. Portadas de Revista Interactivas (Zoom & Drag Lightbox)
- Visor a pantalla completa interactivo con Glassmorphism, soporte para zoom (rueda de ratón, controles UI) y paneo para portadas impresas.

---

## 🔐 Seguridad

### Login URL personalizada (`/turpial`)
- La URL de inicio de sesión por defecto (`/wp-login.php`) fue movida a `/turpial`. Respuestas 404 para escaneos genéricos.

### Sanitización y Prevención XSS / SQLi
- Sanitización estricta de variables en formularios, búsquedas (`esc_attr()`, `esc_html()`, `esc_url_raw()`), bloques JSON-LD (`wp_json_encode()`), y Nonces para todas las llamadas AJAX.

---

## ⚡ Performance

### Transient Caching
- **`pro_latest_post_date`** (60s) para polling de noticias.
- **`pro_ticker_posts`** (5m) para el ticker de "Último Minuto". 
Se invalidan automáticamente mediante el hook `transition_post_status`.

### Cache Busting & Queries Optimizadas
- Cache Busting nativo usando `filemtime()` en recursos estáticos.
- Queries optimizadas, devolviendo solo los IDs requeridos.

---

## 🛠 Instalación y Configuración

1. **Subir el Tema:** Copia la carpeta del tema en `wp-content/themes/` de tu WordPress.
2. **Compilar Assets (Si modificas el código fuente):**
   ```bash
   npm install
   npm run build
   ```
3. **Activar el Tema:** Desde el panel de WordPress (*Apariencia > Temas*).
4. **Instalación Nuclear Autónoma:** Al activarse y entrar al panel por primera vez, el sistema creará automáticamente las páginas legales, categorías, menú de navegación genérico y configurará las tablas personalizadas del módulo SSIVO-SEO.
5. **Guardar Enlaces Permanentes:** Ve a *Ajustes > Enlaces permanentes* y haz clic en **Guardar cambios**.

---

## 🔄 Changelog

### v2.0.0 — Refactorización Espressivo & Integración SSIVO-SEO (Sesión Actual)
- ✅ **Marca Blanca (White-label):** Se reemplazaron las dependencias semánticas previas por nomenclatura genérica ("Espressivo", "MerchanDev").
- ✅ **SSIVO-SEO Integrado:** Incorporación del motor de posicionamiento avanzado (React/PHP) sin depender de plugins de terceros.
- ✅ **Tabla Indexable Autónoma:** Creación de `ssivo_seo_indexable` para proteger el rendimiento de la base de datos de WordPress.
- ✅ **Panel lateral Gutenberg:** UI nativa para calcular legibilidad en tiempo real.

### v1.3.0 — Mejoras Estructurales y Legales
- ✅ Plantillas de "Términos y Condiciones" y "Política de Cookies" autogenerables.
- ✅ Reorganización del Footer y sustracción de meta tags de la página de inicio.

### v1.2.0 — Auditoría y funcionalidades
- ✅ Lightbox interactivo con Zoom & Pan para portadas.
- ✅ Buscador predictivo AJAX con validaciones de seguridad.
- ✅ Sistema de ticker "Último Minuto" con transient caching.

### v1.1.0 — Auditoría de seguridad
- ✅ Sanitización completa de inputs, nonces y guards ABSPATH.
- ✅ Login URL personalizada `/turpial` con matching exacto.

---

*Diseñado y desarrollado por **Merchan.Dev & Espressivo Venezuela, C.A** — Framework editorial SaaS premium para medios digitales e impresos.*
*Web: [merchan.dev](https://merchan.dev)*
