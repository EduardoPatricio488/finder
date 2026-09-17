(function () {
    'use strict';

    const sectionSelector = '[wire\\:key^="builder-section-"]';
    const builderSelector = '[wire\\:id]';
    const state = {
        ready: false,
        layersOpen: false,
        grid: false,
    };

    const isBuilder = () => {
        const path = window.location.pathname;
        return Boolean(document.querySelector(builderSelector)) && path.includes('/websites/') && path.endsWith('/builder');
    };

    const root = () => document.querySelector(builderSelector);
    const wire = () => {
        const element = root();
        return element && window.Livewire ? window.Livewire.find(element.getAttribute('wire:id')) : null;
    };
    const sections = () => Array.from(document.querySelectorAll(sectionSelector));

    const getData = () => {
        const app = wire();
        try {
            return Array.isArray(app?.get?.('sections')) ? app.get('sections') : [];
        } catch (error) {
            return [];
        }
    };

    const px = value => Number.parseFloat(value) || 0;

    const applySectionSettings = () => {
        const data = getData();
        sections().forEach((section, index) => {
            const model = data[index] || {};
            const settings = model.settings || {};
            const type = model.type || 'text';
            section.dataset.builderSectionType = type;
            section.dataset.builderSectionIndex = String(index);

            section.style.borderBottom = '0';
            section.style.outline = 'none';
            section.style.boxShadow = 'none';
            section.style.background = settings.background && settings.background !== 'transparent' ? settings.background : '';

            const paddingMap = { sm: '3rem', md: '4.5rem', lg: '6rem', xl: '8rem' };
            if (settings.padding && paddingMap[settings.padding]) {
                section.style.paddingTop = paddingMap[settings.padding];
                section.style.paddingBottom = paddingMap[settings.padding];
            }

            const radiusMap = { none: '0', sm: '0.75rem', md: '1.5rem', lg: '2rem' };
            if (settings.radius && radiusMap[settings.radius]) section.style.borderRadius = radiusMap[settings.radius];

            const shadowMap = {
                none: 'none',
                sm: '0 8px 30px rgba(0,0,0,.08)',
                md: '0 20px 60px rgba(0,0,0,.12)',
            };
            if (settings.shadow && shadowMap[settings.shadow]) section.style.boxShadow = shadowMap[settings.shadow];

            const align = settings.align || 'left';
            section.style.setProperty('--finder-section-align', align);
        });
    };

    const selectedElement = () => document.querySelector('.finder-canva-selected-element');

    const getElementMeta = element => {
        const section = element?.closest(sectionSelector);
        if (!section) return null;
        const index = sections().indexOf(section);
        const type = section.dataset.builderSectionType || 'text';
        const headings = Array.from(section.querySelectorAll('h1,h2,h3')).filter(node => !node.closest('button,a'));
        const paragraphs = Array.from(section.querySelectorAll('p')).filter(node => !node.closest('button,a'));
        const articles = Array.from(section.querySelectorAll('article'));
        if (headings[0] === element) return { index, slot: 'title' };
        if (type === 'hero' && paragraphs[0] === element) return { index, slot: 'subtitle' };
        if (type === 'hero' && paragraphs[1] === element) return { index, slot: 'description' };
        if (paragraphs[0] === element && type !== 'hero') return { index, slot: type === 'text' ? 'body' : 'description' };
        const article = element.closest('article');
        if (article) {
            const itemIndex = articles.indexOf(article);
            const itemHeading = article.querySelector('p.font-black, h3, h4');
            const itemParagraph = article.querySelector('p.mt-2, p:not(.font-black)');
            if (itemHeading === element) {
                const field = type === 'faq' ? 'question' : (type === 'testimonials' ? 'name' : 'title');
                return { index, slot: `item.${itemIndex}.${field}` };
            }
            if (itemParagraph === element) {
                const field = type === 'faq' ? 'answer' : (type === 'testimonials' ? 'quote' : (type === 'pricing' ? 'price' : 'description'));
                return { index, slot: `item.${itemIndex}.${field}` };
            }
        }
        return null;
    };

    const persist = (meta, key, value) => {
        const app = wire();
        if (!app || !meta) return;
        try {
            app.set(`sections.${meta.index}.settings.elements.${meta.slot}.${key}`, value);
        } catch (error) {
            // Keep the visual change even if the persistence request is unavailable.
        }
    };

    const moveSelected = (dx, dy) => {
        const element = selectedElement();
        const meta = getElementMeta(element);
        if (!element || !meta) return;
        const transform = getComputedStyle(element).transform;
        let x = 0;
        let y = 0;
        if (transform && transform !== 'none') {
            const values = transform.match(/matrix(3d)?\\(([^)]+)\\)/);
            if (values) {
                const matrix = values[2].split(',').map(Number);
                x = values[1] === '3d' ? (matrix[12] || 0) : (matrix[4] || 0);
                y = values[1] === '3d' ? (matrix[13] || 0) : (matrix[5] || 0);
            }
        }
        x += dx;
        y += dy;
        element.style.position = 'relative';
        element.style.transform = `translate3d(${Math.round(x)}px, ${Math.round(y)}px, 0)`;
        persist(meta, 'x', Math.round(x));
        persist(meta, 'y', Math.round(y));
        window.dispatchEvent(new Event('resize'));
    };

    const panel = (id, className) => {
        let element = document.getElementById(id);
        if (!element) {
            element = document.createElement('div');
            element.id = id;
            element.className = className;
            document.body.appendChild(element);
        }
        return element;
    };

    const openLayers = () => {
        const element = panel('finder-builder-canva-layers', 'fixed bottom-[76px] left-1/2 z-[255] hidden w-[310px] -translate-x-1/2 overflow-hidden rounded-[24px] border border-zinc-200 bg-white shadow-[0_24px_70px_rgba(0,0,0,.18)]');
        element.innerHTML = `<div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3"><div><div class="text-[9px] font-black uppercase tracking-[.15em] text-zinc-400">Camadas</div><div class="mt-1 text-sm font-black">Elementos da página</div></div><button type="button" data-close class="rounded-lg px-2 py-1 text-lg text-zinc-400">×</button></div><div data-layers class="max-h-[44vh] overflow-y-auto p-2"></div>`;
        const list = element.querySelector('[data-layers]');
        const data = getData();
        const nodes = sections();
        nodes.forEach((section, index) => {
            const type = data[index]?.type || `Secção ${index + 1}`;
            const sectionButton = document.createElement('button');
            sectionButton.type = 'button';
            sectionButton.className = 'mb-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left hover:bg-zinc-50';
            sectionButton.innerHTML = `<span class="flex h-7 w-7 items-center justify-center rounded-lg bg-zinc-100 text-[10px] font-black">${index + 1}</span><span class="min-w-0 flex-1 truncate text-xs font-bold">${String(type).replaceAll('<', '&lt;')}</span><span class="text-[10px] text-zinc-400">Secção</span>`;
            sectionButton.addEventListener('click', () => {
                section.scrollIntoView({ behavior: 'smooth', block: 'center' });
                section.querySelector('h1,h2,h3,p')?.dispatchEvent(new MouseEvent('click', { bubbles: true }));
                closeLayers();
            });
            list.appendChild(sectionButton);
        });
        element.querySelector('[data-close]').addEventListener('click', closeLayers);
        element.classList.remove('hidden');
        state.layersOpen = true;
    };

    const closeLayers = () => {
        const element = document.getElementById('finder-builder-canva-layers');
        if (!element) return;
        element.classList.add('hidden');
        state.layersOpen = false;
    };

    const openElements = () => {
        const palette = document.getElementById('finder-builder-canva-palette');
        if (!palette) return;
        palette.classList.remove('hidden');
        palette.classList.add('flex');
    };

    const toggleGrid = () => {
        state.grid = !state.grid;
        document.body.classList.toggle('finder-canva-grid-active', state.grid);
    };

    const buildDock = () => {
        if (document.getElementById('finder-builder-canva-dock')) return;
        const dock = document.createElement('div');
        dock.id = 'finder-builder-canva-dock';
        dock.className = 'fixed bottom-4 left-1/2 z-[250] flex -translate-x-1/2 items-center gap-1 rounded-[18px] border border-zinc-200 bg-white/95 p-1.5 shadow-[0_18px_50px_rgba(0,0,0,.14)] backdrop-blur-xl';
        dock.innerHTML = `
            <button type="button" data-tool="elements"><span>＋</span>Elementos</button>
            <button type="button" data-tool="undo">↶</button>
            <button type="button" data-tool="redo">↷</button>
            <span class="mx-1 h-6 w-px bg-zinc-200"></span>
            <button type="button" data-tool="layers"><span>▤</span>Camadas</button>
            <button type="button" data-tool="grid"><span>▦</span>Guias</button>
            <button type="button" data-tool="center"><span>⌗</span>Centrar</button>
        `;
        dock.querySelectorAll('[data-tool]').forEach(button => button.addEventListener('click', () => {
            const tool = button.dataset.tool;
            const app = wire();
            if (tool === 'elements') openElements();
            if (tool === 'undo') app?.undo();
            if (tool === 'redo') app?.redo();
            if (tool === 'layers') state.layersOpen ? closeLayers() : openLayers();
            if (tool === 'grid') toggleGrid();
            if (tool === 'center') {
                const element = selectedElement();
                const meta = getElementMeta(element);
                if (!element || !meta) return;
                element.style.transform = 'translate3d(0,0,0)';
                persist(meta, 'x', 0);
                persist(meta, 'y', 0);
            }
        }));
        document.body.appendChild(dock);
    };

    const keyboard = event => {
        if (!isBuilder()) return;
        const target = event.target;
        const typing = target?.isContentEditable || ['input', 'textarea', 'select'].includes(String(target?.tagName || '').toLowerCase());
        const modifier = event.ctrlKey || event.metaKey;

        if (modifier && event.shiftKey && event.key.toLowerCase() === 'g') {
            event.preventDefault();
            toggleGrid();
            return;
        }

        if (typing) return;

        const step = event.shiftKey ? 10 : 1;
        if (event.key === 'ArrowLeft') { event.preventDefault(); moveSelected(-step, 0); }
        if (event.key === 'ArrowRight') { event.preventDefault(); moveSelected(step, 0); }
        if (event.key === 'ArrowUp') { event.preventDefault(); moveSelected(0, -step); }
        if (event.key === 'ArrowDown') { event.preventDefault(); moveSelected(0, step); }
    };

    const install = () => {
        if (!isBuilder()) return;
        applySectionSettings();
        buildDock();
        if (!state.ready) notifyReady();
        state.ready = true;
    };

    const notifyReady = () => {
        const host = panel('finder-builder-canva-ready', 'pointer-events-none fixed bottom-[72px] left-1/2 z-[260] -translate-x-1/2');
        host.innerHTML = '<div class="rounded-full border border-zinc-200 bg-white/95 px-4 py-2 text-[10px] font-black text-zinc-700 shadow-xl backdrop-blur">Canva Mode · selecciona, arrasta e personaliza</div>';
        setTimeout(() => host.remove(), 2600);
    };

    document.addEventListener('keydown', keyboard);

    const style = document.createElement('style');
    style.id = 'finder-builder-canva-runtime-styles';
    style.textContent = `
        ${sectionSelector} { border-bottom: 0 !important; box-shadow: none !important; }
        ${sectionSelector}:hover { background-image: none !important; }
        .finder-canva-selected-element { box-shadow: 0 0 0 1px rgba(99,102,241,.25), 0 0 0 4px rgba(99,102,241,.08) !important; }
        #finder-builder-canva-dock button { display:inline-flex; align-items:center; gap:6px; height:34px; border-radius:11px; padding:0 10px; color:#52525b; font-size:10px; font-weight:900; transition:background-color .16s ease,color .16s ease,transform .16s ease; }
        #finder-builder-canva-dock button:hover { background:#f4f4f5; color:#18181b; transform:translateY(-1px); }
        #finder-builder-canva-dock button span { font-size:13px; color:#6366f1; }
        .finder-canva-grid-active main::before { content:''; position:fixed; inset:68px 0 0; pointer-events:none; z-index:70; background-image:linear-gradient(rgba(99,102,241,.06) 1px,transparent 1px),linear-gradient(90deg,rgba(99,102,241,.06) 1px,transparent 1px); background-size:24px 24px; }
        @media (max-width:1023px) { #finder-builder-canva-dock { bottom:78px; max-width:calc(100vw - 20px); overflow-x:auto; } #finder-builder-canva-dock button { padding:0 8px; } }
        @media (prefers-reduced-motion:reduce) { #finder-builder-canva-dock button { transition:none !important; } }
    `;
    document.head.appendChild(style);

    const boot = () => {
        if (!isBuilder()) return;
        install();
        if (window.Livewire) Livewire.hook('morph.updated', () => setTimeout(install, 55));
    };

    if (window.Livewire) boot();
    else document.addEventListener('livewire:init', boot, { once: true });
})();
