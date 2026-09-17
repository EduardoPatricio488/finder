(function () {
    'use strict';

    const ROOT = '[wire\\:id]';
    const SECTION = '[wire\\:key^="builder-section-"]';
    const state = { element: null, overlay: null, toolbar: null, drag: null, resize: null, rotate: null, clipboard: null, listeners: false };

    const isBuilder = () => Boolean(document.querySelector(ROOT)) && /\/websites\/[^/]+\/builder$/.test(location.pathname);
    const root = () => document.querySelector(ROOT);
    const wire = () => { const node = root(); return node && window.Livewire ? window.Livewire.find(node.getAttribute('wire:id')) : null; };
    const sections = () => [...document.querySelectorAll(SECTION)];

    const getMeta = element => {
        const section = element?.closest(SECTION);
        if (!section) return null;
        const index = sections().indexOf(section);
        const type = section.dataset.builderSectionType || '';
        const headings = [...section.querySelectorAll('h1,h2,h3')].filter(n => !n.closest('button,a'));
        const paragraphs = [...section.querySelectorAll('p')].filter(n => !n.closest('button,a'));
        const articles = [...section.querySelectorAll('article')];
        if (headings[0] === element) return { index, slot: 'title' };
        if (type === 'hero' && paragraphs[0] === element) return { index, slot: 'subtitle' };
        if (type === 'hero' && paragraphs[1] === element) return { index, slot: 'description' };
        if (paragraphs[0] === element) return { index, slot: type === 'text' ? 'body' : 'description' };
        const article = element.closest('article');
        if (article) {
            const item = articles.indexOf(article);
            const h = article.querySelector('p.font-black,h3,h4');
            const p = article.querySelector('p.mt-2,p:not(.font-black)');
            if (h === element) return { index, slot: `item.${item}.${type === 'faq' ? 'question' : type === 'testimonials' ? 'name' : 'title'}` };
            if (p === element) return { index, slot: `item.${item}.${type === 'faq' ? 'answer' : type === 'testimonials' ? 'quote' : type === 'pricing' ? 'price' : 'description'}` };
        }
        if (element.matches('img')) return { index, slot: 'image' };
        return null;
    };

    const store = (element, values) => {
        const meta = getMeta(element);
        const app = wire();
        if (!meta || !app) return;
        Object.entries(values).forEach(([key, value]) => {
            try { app.set(`sections.${meta.index}.settings.elements.${meta.slot}.${key}`, value); } catch {}
        });
    };

    const transform = element => {
        const [x,y,r] = String(element.dataset.canvaUltraTransform || '0,0,0').split(',').map(Number);
        return { x: Number.isFinite(x) ? x : 0, y: Number.isFinite(y) ? y : 0, r: Number.isFinite(r) ? r : 0 };
    };

    const setTransform = (element, values, persist = false) => {
        const next = { ...transform(element), ...values };
        element.dataset.canvaUltraTransform = `${Math.round(next.x)},${Math.round(next.y)},${Math.round(next.r)}`;
        element.style.position = 'relative';
        element.style.transform = `translate3d(${Math.round(next.x)}px,${Math.round(next.y)}px,0) rotate(${Math.round(next.r)}deg)`;
        if (persist) store(element, { x: Math.round(next.x), y: Math.round(next.y), rotation: Math.round(next.r) });
    };

    const applyStored = () => {
        const app = wire();
        if (!app?.get) return;
        let data = [];
        try { data = app.get('sections') || []; } catch { return; }
        if (!Array.isArray(data)) return;
        sections().forEach((section, index) => {
            const map = data[index]?.settings?.elements || {};
            const nodes = [];
            const headings = [...section.querySelectorAll('h1,h2,h3')].filter(n => !n.closest('button,a'));
            const paragraphs = [...section.querySelectorAll('p')].filter(n => !n.closest('button,a'));
            const type = section.dataset.builderSectionType || '';
            if (headings[0]) nodes.push([headings[0], 'title']);
            if (paragraphs[0]) nodes.push([paragraphs[0], type === 'hero' ? 'subtitle' : type === 'text' ? 'body' : 'description']);
            if (paragraphs[1]) nodes.push([paragraphs[1], 'description']);
            [...section.querySelectorAll('article')].forEach((article, item) => {
                const h = article.querySelector('p.font-black,h3,h4');
                const p = article.querySelector('p.mt-2,p:not(.font-black)');
                if (h) nodes.push([h, `item.${item}.${type === 'faq' ? 'question' : type === 'testimonials' ? 'name' : 'title'}`]);
                if (p) nodes.push([p, `item.${item}.${type === 'faq' ? 'answer' : type === 'testimonials' ? 'quote' : type === 'pricing' ? 'price' : 'description'}`]);
            });
            section.querySelectorAll('img').forEach((img, imgIndex) => nodes.push([img, imgIndex ? `image.${imgIndex}` : 'image']));
            nodes.forEach(([node, slot]) => {
                const saved = map[slot] || {};
                const x = Number(saved.x || 0), y = Number(saved.y || 0), r = Number(saved.rotation || 0);
                node.dataset.canvaUltraTransform = `${x},${y},${r}`;
                if (x || y || r) setTransform(node, { x, y, r }, false);
                if (saved.width) node.style.width = saved.width;
                if (saved.height) node.style.height = saved.height;
                if (saved.opacity != null) node.style.opacity = saved.opacity;
                if (saved.zIndex != null) node.style.zIndex = saved.zIndex;
            });
        });
    };

    const notify = message => window.dispatchEvent(new CustomEvent('finder-canva-notify', { detail: { message } }));
    const selected = () => state.element;

    const selectionTarget = event => {
        const target = event.target?.closest('.finder-canva-editable,.finder-canva-image,h1,h2,h3,p,img');
        return target?.closest(SECTION) ? target : null;
    };

    const clearSelection = () => {
        document.querySelectorAll('.finder-canva-ultra-selected').forEach(n => n.classList.remove('finder-canva-ultra-selected'));
        state.element = null;
        state.overlay?.classList.remove('is-visible');
        state.toolbar?.classList.remove('is-visible');
    };

    const ensureTransform = element => {
        if (element.dataset.canvaUltraTransform) return;
        let x = 0, y = 0, r = 0;
        try {
            const meta = getMeta(element);
            const app = wire();
            const base = meta && app?.get ? app.get(`sections.${meta.index}.settings.elements.${meta.slot}`) || {} : {};
            x = Number(base.x || 0); y = Number(base.y || 0); r = Number(base.rotation || 0);
        } catch {}
        element.dataset.canvaUltraTransform = `${x},${y},${r}`;
    };

    const select = element => {
        if (!element) return;
        ensureTransform(element);
        document.querySelectorAll('.finder-canva-ultra-selected').forEach(n => n.classList.remove('finder-canva-ultra-selected'));
        state.element = element;
        element.classList.add('finder-canva-ultra-selected');
        positionUI();
    };

    const canvas = () => document.querySelector('[data-finder-site-canvas]') || sections()[0]?.parentElement;
    const canvasRect = () => canvas()?.getBoundingClientRect();

    const positionUI = () => {
        if (!state.element || !document.body.contains(state.element)) return clearSelection();
        const rect = state.element.getBoundingClientRect();
        const overlay = state.overlay;
        if (overlay) {
            overlay.style.left = `${rect.left - 2}px`;
            overlay.style.top = `${rect.top - 2}px`;
            overlay.style.width = `${Math.max(2, rect.width + 4)}px`;
            overlay.style.height = `${Math.max(2, rect.height + 4)}px`;
            overlay.classList.add('is-visible');
        }
        if (state.toolbar) {
            const width = 350;
            state.toolbar.style.left = `${Math.max(8, Math.min(innerWidth - width - 8, rect.left + rect.width / 2 - width / 2))}px`;
            state.toolbar.style.top = `${Math.max(72, rect.top - 48)}px`;
            state.toolbar.classList.add('is-visible');
        }
    };

    const createUI = () => {
        if (!state.overlay) {
            const overlay = document.createElement('div');
            overlay.id = 'finder-canva-ultra-overlay';
            overlay.innerHTML = '<div data-h="nw"></div><div data-h="n"></div><div data-h="ne"></div><div data-h="e"></div><div data-h="se"></div><div data-h="s"></div><div data-h="sw"></div><div data-h="w"></div><div data-h="rotate">↻</div>';
            overlay.querySelectorAll('[data-h]').forEach(handle => handle.addEventListener('pointerdown', startHandle));
            document.body.appendChild(overlay);
            state.overlay = overlay;
        }
        if (!state.toolbar) {
            const toolbar = document.createElement('div');
            toolbar.id = 'finder-canva-ultra-toolbar';
            toolbar.innerHTML = '<button data-a="copy">Copiar</button><button data-a="paste">Colar</button><i></i><button data-a="front">Trazer</button><button data-a="back">Enviar</button><button data-a="left">←</button><button data-a="center">Centro</button><button data-a="right">→</button><button data-a="reset">Reset</button>';
            toolbar.querySelectorAll('button').forEach(button => button.addEventListener('click', () => toolbarAction(button.dataset.a)));
            document.body.appendChild(toolbar);
            state.toolbar = toolbar;
        }
    };

    const snap = (value, step = 8) => Math.round(value / step) * step;
    const startDrag = event => {
        const element = state.element;
        if (!element || event.button !== 0) return;
        if (event.target.closest('button,a,input,textarea,select,[contenteditable="true"]')) return;
        ensureTransform(element);
        state.drag = { sx: event.clientX, sy: event.clientY, start: transform(element) };
        element.setPointerCapture?.(event.pointerId);
        event.preventDefault();
    };

    const moveDrag = event => {
        if (!state.drag || !state.element) return;
        let x = state.drag.start.x + (event.clientX - state.drag.sx);
        let y = state.drag.start.y + (event.clientY - state.drag.sy);
        if (!event.altKey) { x = snap(x); y = snap(y); }
        const host = canvasRect();
        const rect = state.element.getBoundingClientRect();
        if (host) {
            const cx = rect.left + rect.width / 2;
            const cy = rect.top + rect.height / 2;
            if (Math.abs(cx - (host.left + host.width / 2)) < 6) x += (host.left + host.width / 2) - cx;
            if (Math.abs(cy - (host.top + host.height / 2)) < 6) y += (host.top + host.height / 2) - cy;
        }
        setTransform(state.element, { x, y, r: state.drag.start.r });
        positionUI();
    };

    const endDrag = () => {
        if (!state.drag || !state.element) return;
        const t = transform(state.element);
        setTransform(state.element, t, true);
        state.drag = null;
    };

    const startHandle = event => {
        if (!state.element) return;
        const kind = event.currentTarget.dataset.h;
        const rect = state.element.getBoundingClientRect();
        ensureTransform(state.element);
        if (kind === 'rotate') {
            state.rotate = { cx: rect.left + rect.width / 2, cy: rect.top + rect.height / 2, start: transform(state.element), sx: event.clientX, sy: event.clientY };
        } else {
            state.resize = { kind, sx: event.clientX, sy: event.clientY, width: rect.width, height: rect.height, start: transform(state.element), font: parseFloat(getComputedStyle(state.element).fontSize) || 16 };
        }
        event.currentTarget.setPointerCapture?.(event.pointerId);
        event.preventDefault();
        event.stopPropagation();
    };

    const moveHandle = event => {
        if (state.rotate && state.element) {
            const a1 = Math.atan2(state.rotate.sy - state.rotate.cy, state.rotate.sx - state.rotate.cx);
            const a2 = Math.atan2(event.clientY - state.rotate.cy, event.clientX - state.rotate.cx);
            let degrees = state.rotate.start.r + (a2 - a1) * 180 / Math.PI;
            if (!event.altKey) degrees = Math.round(degrees / 5) * 5;
            setTransform(state.element, { x: state.rotate.start.x, y: state.rotate.start.y, r: degrees });
            positionUI();
            return;
        }
        if (!state.resize || !state.element) return;
        const dx = event.clientX - state.resize.sx;
        const dy = event.clientY - state.resize.sy;
        let width = state.resize.width;
        let height = state.resize.height;
        const kind = state.resize.kind;
        if (kind.includes('e')) width += dx;
        if (kind.includes('w')) width -= dx;
        if (kind.includes('s')) height += dy;
        if (kind.includes('n')) height -= dy;
        width = Math.max(40, width); height = Math.max(20, height);
        if (event.shiftKey) {
            const ratio = state.resize.width / state.resize.height;
            if (Math.abs(dx) >= Math.abs(dy)) height = width / ratio; else width = height * ratio;
        }
        state.element.style.width = `${Math.round(width)}px`;
        if (state.element.matches('img')) state.element.style.height = `${Math.round(height)}px`;
        else if (state.element.tagName !== 'P') state.element.style.fontSize = `${Math.max(8, Math.min(160, Math.round(state.resize.font * width / state.resize.width)))}px`;
        positionUI();
    };

    const endHandle = () => {
        if (state.rotate && state.element) {
            setTransform(state.element, transform(state.element), true);
        }
        if (state.resize && state.element) {
            store(state.element, { width: state.element.style.width });
            if (state.element.matches('img')) store(state.element, { height: state.element.style.height });
        }
        state.rotate = null;
        state.resize = null;
    };

    const toolbarAction = action => {
        const element = state.element;
        if (!element) return;
        const t = transform(element);
        if (action === 'copy') {
            state.clipboard = { transform: { ...t }, width: element.style.width, height: element.style.height, fontSize: element.style.fontSize, opacity: element.style.opacity, zIndex: element.style.zIndex };
            notify('Estilo do elemento copiado');
        }
        if (action === 'paste' && state.clipboard) {
            Object.assign(element.style, { width: state.clipboard.width, height: state.clipboard.height, fontSize: state.clipboard.fontSize, opacity: state.clipboard.opacity, zIndex: state.clipboard.zIndex });
            setTransform(element, { ...state.clipboard.transform }, true);
            store(element, { width: element.style.width, height: element.style.height, fontSize: element.style.fontSize, opacity: element.style.opacity, zIndex: element.style.zIndex });
            positionUI();
            notify('Estilo aplicado');
        }
        if (action === 'front') { element.style.zIndex = String((Number(getComputedStyle(element).zIndex) || 2) + 1); store(element, { zIndex: element.style.zIndex }); }
        if (action === 'back') { element.style.zIndex = String(Math.max(1, (Number(getComputedStyle(element).zIndex) || 2) - 1)); store(element, { zIndex: element.style.zIndex }); }
        if (action === 'left') setTransform(element, { x: t.x - 24, y: t.y, r: t.r }, true);
        if (action === 'right') setTransform(element, { x: t.x + 24, y: t.y, r: t.r }, true);
        if (action === 'center') setTransform(element, { x: 0, y: 0, r: t.r }, true);
        if (action === 'reset') {
            element.style.width = ''; element.style.height = ''; element.style.fontSize = ''; element.style.opacity = ''; element.style.zIndex = '';
            setTransform(element, { x: 0, y: 0, r: 0 }, true);
        }
        positionUI();
    };

    const keyboard = event => {
        if (!isBuilder() || !state.element) return;
        const typing = event.target?.isContentEditable || ['input','textarea','select'].includes(String(event.target?.tagName || '').toLowerCase());
        if (typing) return;
        const mod = event.ctrlKey || event.metaKey;
        if (mod && event.key.toLowerCase() === 'c') { event.preventDefault(); toolbarAction('copy'); return; }
        if (mod && event.key.toLowerCase() === 'v') { event.preventDefault(); toolbarAction('paste'); return; }
        if (event.key.startsWith('Arrow')) {
            event.preventDefault();
            const step = event.shiftKey ? 10 : 1;
            const t = transform(state.element);
            setTransform(state.element, { x: t.x + (event.key === 'ArrowRight' ? step : event.key === 'ArrowLeft' ? -step : 0), y: t.y + (event.key === 'ArrowDown' ? step : event.key === 'ArrowUp' ? -step : 0), r: t.r }, true);
            positionUI();
        }
    };

    const install = () => {
        if (!isBuilder()) return;
        createUI();
        applyStored();
        if (state.listeners) return;
        state.listeners = true;
        document.addEventListener('pointerdown', event => {
            if (!isBuilder()) return;
            if (event.target.closest('#finder-canva-ultra-overlay,#finder-canva-ultra-toolbar,#finder-builder-canva-pro-lab,#finder-builder-canva-pro-tools')) return;
            const target = selectionTarget(event);
            if (!target) { if (!event.target.closest(SECTION)) clearSelection(); return; }
            select(target);
            startDrag(event);
        }, true);
        document.addEventListener('pointermove', event => { if (state.drag) moveDrag(event); if (state.resize || state.rotate) moveHandle(event); }, true);
        document.addEventListener('pointerup', () => { endDrag(); endHandle(); }, true);
        document.addEventListener('pointercancel', () => { endDrag(); endHandle(); }, true);
        document.addEventListener('keydown', keyboard);
        window.addEventListener('scroll', positionUI, { passive: true });
        window.addEventListener('resize', positionUI, { passive: true });
    };

    const boot = () => {
        if (!isBuilder()) return;
        install();
        if (window.Livewire) Livewire.hook('morph.updated', () => setTimeout(() => { install(); applyStored(); positionUI(); }, 80));
    };

    if (window.Livewire) boot();
    else document.addEventListener('livewire:init', boot, { once: true });
})();