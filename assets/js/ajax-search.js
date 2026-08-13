/**
 * @author Arturo Merchan | Merchan.Dev | Espressivo Venezuela,C.A
 * 
 * ADVERTENCIA LEGAL:
 * Queda totalmente prohibida su reproduccion, edicion, venta, propaganda, alteracion 
 * o cualquier otra accion que de una u otra forma violente la propiedad intelectual, 
 * material y digital de este proyecto. Esta infraccion esta prohibida y penada por la ley.
 */
/**
 * Buscador Predictivo (Ajax Search) para Pro
 */
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('.ajax-search-form');
    const searchInput = document.querySelector('.ajax-search-input');
    const resultsContainer = document.querySelector('.ajax-search-results-container');
    
    if (!searchInput || !resultsContainer || typeof pro_ajax === 'undefined') {
        return;
    }

    let debounceTimer;

    searchInput.addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        const query = e.target.value.trim();

        if (query.length < 3) {
            resultsContainer.style.display = 'none';
            resultsContainer.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetchResults(query);
        }, 500); // 500ms debounce
    });

    // Cerrar resultados si se hace click fuera
    document.addEventListener('click', function(e) {
        if (!searchForm.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.style.display = 'none';
        }
    });

    function fetchResults(query) {
        const formData = new FormData();
        formData.append('action', 'pro_ajax_search');
        formData.append('s', query);
        formData.append('nonce', pro_ajax.nonce);

        fetch(pro_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            if (html.trim() !== '') {
                resultsContainer.innerHTML = html;
                resultsContainer.style.display = 'block';
            } else {
                resultsContainer.style.display = 'none';
            }
        })
        .catch(error => console.error('Error fetching search results:', error));
    }
});
