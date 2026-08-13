import { useSelect } from '@wordpress/data';
import { calculateReadability } from '../utils/readability';

const Sidebar = () => {
    // Escucha el contenido del editor en tiempo real
    const content = useSelect((select) => {
        return select('core/editor').getEditedPostContent();
    }, []);

    // Calcula el puntaje (0 a 100)
    const score = calculateReadability(content);
    
    // Determina el color basado en el puntaje
    const barColor = score > 80 ? '#4caf50' : score > 50 ? '#ff9800' : '#f44336';

    return (
        <div style={{ padding: '16px' }}>
            <h3>Análisis en Tiempo Real</h3>
            <p>Legibilidad del contenido:</p>
            
            <div style={{ background: '#e0e0e0', borderRadius: '4px', height: '20px', width: '100%' }}>
                <div style={{
                    background: barColor,
                    height: '100%',
                    borderRadius: '4px',
                    width: `${score}%`,
                    transition: 'width 0.3s ease'
                }}></div>
            </div>
            
            <p style={{ textAlign: 'right', fontWeight: 'bold' }}>{score}%</p>
            
            {score === 100 && (
                <div style={{ background: '#e8f5e9', color: '#2e7d32', padding: '10px', borderRadius: '4px', marginTop: '10px' }}>
                    ¡Legibilidad 100%! Excelente trabajo.
                </div>
            )}
            
            <hr style={{ margin: '20px 0' }} />
            <p style={{ fontSize: '12px', color: '#666' }}>
                *La meta descripción y configuración avanzada se generarán automáticamente al publicar el artículo.
            </p>
        </div>
    );
};

export default Sidebar;
