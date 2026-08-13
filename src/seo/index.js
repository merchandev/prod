import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar } from '@wordpress/edit-post';
import { createRoot } from '@wordpress/element';
import Sidebar from './components/Sidebar';
import SnippetPreview from './components/SnippetPreview';

// Registrar Barra Lateral
registerPlugin('ssivo-seo-sidebar', {
    render: () => (
        <PluginSidebar name="ssivo-seo-panel" title="SSIVO SEO" icon="chart-bar">
            <Sidebar />
        </PluginSidebar>
    ),
});

// Montar Vista Previa en el Meta Box si existe el contenedor
document.addEventListener('DOMContentLoaded', () => {
    const metaboxRoot = document.getElementById('ssivo-seo-metabox-root');
    if (metaboxRoot) {
        const root = createRoot(metaboxRoot);
        root.render(<SnippetPreview />);
    }
});
