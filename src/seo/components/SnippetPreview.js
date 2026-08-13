import { useState, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { calculateReadability } from '../utils/readability';

const stripHtml = (html) => {
    if (!html) return '';
    let tmp = document.createElement('div');
    tmp.innerHTML = html;
    return (tmp.textContent || tmp.innerText || '').replace(/\s+/g, ' ').trim();
};

const SnippetPreview = () => {
    const rootElement = document.getElementById('ssivo-seo-metabox-root');
    const initialCustomTitle = rootElement?.dataset?.customTitle || '';
    const initialCustomDesc = rootElement?.dataset?.customDesc || '';

    const [customTitle, setCustomTitle] = useState(initialCustomTitle);
    const [customDesc, setCustomDesc] = useState(initialCustomDesc);

    const { postTitle, excerpt, siteName, mediaUrl, content } = useSelect((select) => {
        const coreEditor = select('core/editor');
        const coreSite = select('core');
        
        let siteData = coreSite.getSite();
        let fallbackSiteName = siteData ? siteData.title : 'Espressivo Editorial';

        const mediaId = coreEditor.getEditedPostAttribute('featured_media');
        const media = mediaId ? select('core').getEntityRecord('root', 'media', mediaId) : null;
        const mediaUrl = media ? media.source_url : '';

        return {
            postTitle: coreEditor.getEditedPostAttribute('title'),
            excerpt: coreEditor.getEditedPostAttribute('excerpt'),
            siteName: fallbackSiteName,
            mediaUrl: mediaUrl,
            content: coreEditor.getEditedPostContent()
        };
    }, []);

    const autoDesc = excerpt ? excerpt : stripHtml(content).substring(0, 155);
    const finalTitle = customTitle.trim() !== '' ? customTitle : (postTitle || 'Título del Artículo');
    const finalDesc = customDesc.trim() !== '' ? customDesc : (autoDesc || 'Esta es una descripción generada automáticamente basada en el extracto del artículo para mostrar en los resultados de búsqueda.');

    const score = calculateReadability(content || '');
    const barColor = score > 80 ? '#4caf50' : score > 50 ? '#ff9800' : '#f44336';

    return (
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '30px', padding: '10px 0' }}>
            
            {/* Columna Izquierda: Formulario de Edición */}
            <div>
                <h4 style={{ marginTop: 0, marginBottom: '15px', color: '#1e293b', fontSize: '15px' }}>Personalizar SEO</h4>
                
                <div style={{ marginBottom: '20px' }}>
                    <label style={{ display: 'block', fontWeight: '600', marginBottom: '8px', color: '#475569' }}>
                        Título SEO Personalizado
                    </label>
                    <input 
                        type="text" 
                        name="ssivo_seo_custom_title" 
                        value={customTitle} 
                        onChange={(e) => setCustomTitle(e.target.value)} 
                        style={{ width: '100%', padding: '8px', border: '1px solid #cbd5e1', borderRadius: '4px' }}
                        placeholder={postTitle || "Escribe un título atractivo (Opcional)"}
                    />
                    <p style={{ margin: '4px 0 0 0', fontSize: '12px', color: '#64748b' }}>
                        Si lo dejas en blanco, se usará el título automático mostrado arriba.
                    </p>
                </div>

                <div>
                    <label style={{ display: 'block', fontWeight: '600', marginBottom: '8px', color: '#475569' }}>
                        Descripción SEO Personalizada (Meta Description)
                    </label>
                    <textarea 
                        name="ssivo_seo_custom_desc" 
                        value={customDesc} 
                        onChange={(e) => setCustomDesc(e.target.value)} 
                        style={{ width: '100%', height: '100px', padding: '8px', border: '1px solid #cbd5e1', borderRadius: '4px' }}
                        placeholder={autoDesc || "Escribe una descripción cautivadora (Opcional)"}
                    />
                    <p style={{ margin: '4px 0 0 0', fontSize: '12px', color: '#64748b' }}>
                        Si lo dejas en blanco, se usará el resumen automático mostrado arriba.
                    </p>
                </div>
            </div>

            {/* Columna Derecha: Vista Previa y Legibilidad */}
            <div style={{ background: '#f8fafc', padding: '20px', borderRadius: '8px', border: '1px solid #e2e8f0' }}>
                
                {/* Legibilidad */}
                <div style={{ marginBottom: '25px' }}>
                    <h4 style={{ marginTop: 0, marginBottom: '10px', color: '#1e293b', fontSize: '15px' }}>Análisis de Legibilidad</h4>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                        <div style={{ flexGrow: 1, background: '#e0e0e0', borderRadius: '4px', height: '20px' }}>
                            <div style={{
                                background: barColor,
                                height: '100%',
                                borderRadius: '4px',
                                width: `${score}%`,
                                transition: 'width 0.3s ease'
                            }}></div>
                        </div>
                        <span style={{ fontWeight: 'bold', color: barColor, minWidth: '40px' }}>{score}%</span>
                    </div>
                    {score === 100 && (
                        <div style={{ background: '#e8f5e9', color: '#2e7d32', padding: '8px 12px', borderRadius: '4px', marginTop: '10px', fontSize: '13px', fontWeight: 'bold' }}>
                            ¡Legibilidad 100%! Excelente trabajo.
                        </div>
                    )}
                </div>

                <hr style={{ border: '0', borderTop: '1px solid #cbd5e1', margin: '20px 0' }} />

                <h4 style={{ marginTop: 0, marginBottom: '20px', color: '#1e293b', fontSize: '15px', display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <span className="dashicons dashicons-search" style={{ color: '#3b82f6' }}></span>
                    Vista Previa en Google
                </h4>
                
                {/* Simulador de Google */}
                <div style={{ fontFamily: 'arial, sans-serif', maxWidth: '600px' }}>
                    <div style={{ fontSize: '14px', color: '#202124', display: 'flex', alignItems: 'center', gap: '5px', marginBottom: '5px' }}>
                        <div style={{ width: '28px', height: '28px', background: '#e2e8f0', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '12px' }}>🌐</div>
                        <div>
                            <span style={{ display: 'block', lineHeight: '1.2' }}>{siteName}</span>
                            <span style={{ color: '#4d5156', fontSize: '12px' }}>https://tusitio.com › categoria › articulo</span>
                        </div>
                    </div>
                    
                    <div style={{ display: 'flex', gap: '15px', alignItems: 'flex-start' }}>
                        <div style={{ flexGrow: 1 }}>
                            <h3 style={{ margin: '0 0 5px 0', fontSize: '20px', color: '#1a0dab', fontWeight: 'normal', lineHeight: '1.3' }}>
                                {finalTitle} | {siteName}
                            </h3>
                            <p style={{ margin: 0, fontSize: '14px', color: '#4d5156', lineHeight: '1.58' }}>
                                {finalDesc.length > 155 ? finalDesc.substring(0, 155) + '...' : finalDesc}
                            </p>
                        </div>
                        {mediaUrl && (
                            <div style={{ flexShrink: 0 }}>
                                <img src={mediaUrl} alt="Destacada" style={{ width: '104px', height: '104px', objectFit: 'cover', borderRadius: '8px', border: '1px solid #e2e8f0' }} />
                            </div>
                        )}
                    </div>
                </div>
            </div>

        </div>
    );
};

export default SnippetPreview;
