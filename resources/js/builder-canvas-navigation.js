(function () {
    'use strict';

    const ROOT = '[wire\\:id]';
    const state = { panning: false, startX: 0, startY: 0, scrollLeft: 0, scrollTop: 0 };

    const isBuilder = () => Boolean(document.querySelector(ROOT)) && /\/websites\/[^/]+\/builder$/.test(location.pathname);

    const scrollHost = () => {
        const canvas = document.querySelector('[data-finder-site-canvas]');
        if (!canvas) return null;
        return canvas.closest('.overflow-auto') || canvas.closest('[class*="overflow-auto"]') || canvas.parentElement;
    };

    const fitCanvas = () => {
        const host = scrollHost();
        const canvas = document.querySelector('[data-finder-site-canvas]');
        if (!host || !canvas) return;
        const left = Math.max(0, (host.scrollWidth - host.clientWidth) / 2);
        const top = Math.max(0, (host.scrollHeight - host.clientHeight) / 6);
        host.scrollTo({ left, top, behavior: 'smooth' });
    };

    const startPan = event => {
        if (!isBuilder() || !event.isPrimary || event.button !== 0 || !event.currentTarget) return;
        if (!event.getModifierState?.(' ')) return;
        const host = scrollHost();
        if (!host) return;
        state.panning = true;
        state.startX = event.clientX;
        state.startY = event.clientY;
        state.scrollLeft = host.scrollLeft;
        state.scrollTop = host.scrollTop;
        host.classList.add('finder-canvas-panning');
        host.setPointerCapture?.(event.pointerId);
        event.preventDefault();
    };

    const movePan = event => {
        if (!state.panning) return;
        const host = scrollHost();
        if (!host) return;
        host.scrollLeft = state.scrollLeft - (event.clientX - state.startX);
        host.scrollTop = state.scrollTop - (event.clientY - state.startY);
        event.preventDefault();
    };

    const endPan = () => {
        state.panning = false;
        scrollHost()?.classList.remove('finder-canvas-panning');
    };

    const wheel = event => {
        if (!isBuilder() || !(event.ctrlKey || event.metaKey)) return;
        const zoomControls = document.querySelector('#finder-ultimate-zoom');
        if (!zoomControls) return;
        event.preventDefault();
        const button = event.deltaY < 0
            ? zoomControls.querySelector('[data-zoom-in], button:last-of-type')
            : zoomControls.querySelector('[data-zoom-out], button:first-of-type');
        button?.click();
    };

    const install = () => {
        if (!isBuilder()) return;
        const host = scrollHost();
        if (!host || host.dataset.finderCanvasNavigationReady === '1') return;
        host.dataset.finderCanvasNavigationReady = '1';
        host.addEventListener('pointerdown', startPan);
        host.addEventListener('pointermove', movePan);
        host.addEventListener('pointerup', endPan);
        host.addEventListener('pointercancel', endPan);
        host.addEventListener('pointerleave', event => { if (state.panning) movePan(event); });
        host.addEventListener('dblclick', event => {
            if (event.target.closest('[data-finder-site-canvas]')) fitCanvas();
        });
        document.addEventListener('wheel', wheel, { passive: false });
    };

    const boot = () => {
        if (!isBuilder()) return;
        install();
        if (window.Livewire) Livewire.hook('morph.updated', () => setTimeout(install, 80));
    };

    if (window.Livewire) boot();
    else document.addEventListener('livewire:init', boot, { once: true });
})();
