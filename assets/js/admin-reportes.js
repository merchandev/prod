(() => {
    'use strict';

    const form = document.querySelector('.erp-form');
    const customDates = document.querySelector('[data-erp-custom-dates]');

    if (!form || !customDates) {
        return;
    }

    const toggleCustomDates = () => {
        const selected = form.querySelector('input[name="period"]:checked');
        const isCustom = Boolean(selected && selected.value === 'custom');

        customDates.hidden = !isCustom;

        customDates.querySelectorAll('input').forEach((input) => {
            input.required = isCustom;
        });
    };

    form.addEventListener('change', (event) => {
        if (event.target instanceof HTMLInputElement && event.target.name === 'period') {
            toggleCustomDates();
        }
    });

    toggleCustomDates();
})();
