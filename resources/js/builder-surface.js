(function () {
    'use strict';

    const builderRoot = () => document.querySelector('[wire\\:id]');
    const sections = () => Array.from(document.querySelectorAll('[wire\\:key^="builder-section-"]'));

    const markCanvas = () => {
        const root = builderRoot();
        if (!root) return;

        root.classList.add('finder-builder-surface');

        let canvas = root.querySelector('[data-finder-site-canvas]');
        if (!canvas) {
            const candidate = Array.from(root.querySelectorAll('div')).find(element => element.classList.contains('rounded-[26px]') && element.classList.contains('bg-white'));
            if (candidate) {
                candidate.setAttribute('data-finder-site-canvas', 'true');
                canvas = candidate;
            }
        }

        sections().forEach(section => section.classList.add('finder-builder-continuous-section'));
    };

    const syncSelection = () => {
        const root = builderRoot();
        const app = root && window.Livewire ? window.Livewire.find(root.getAttribute('wire:id')) : null;
        const selected = Number(app?.selectedSection);

        sections().forEach((section, index) => {
            section.toggleAttribute('data-builder-selected', Number.isInteger(selected) && index === selected);
        });
    };

    const install = () => {
        if (!builderRoot()) return;
        markCanvas();
        syncSelection();
    };

    if (window.Livewire) {
        install();
        Livewire.hook('morph.updated', () => setTimeout(install, 40));
    } else {
        document.addEventListener('livewire:init', install, { once: true });
    }

    document.addEventListener('click', event => {
        const section = event.target.closest('[wire\\:key^="builder-section-"]');
        if (!section) return;
        setTimeout(syncSelection, 30);
    });
})();
