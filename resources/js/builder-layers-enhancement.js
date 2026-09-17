(function () {
    'use strict';

    const ROOT = '[wire\\:id]';
    const SECTION = '[wire\\:key^="builder-section-"]';
    const state = { observer: null };

    const isBuilder = () => Boolean(document.querySelector(ROOT)) && /\/websites\/[^/]+\/builder$/.test(location.pathname);
    const sections = () => [...document.querySelectorAll(SECTION)];
    const escape = value => String(value ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));

    const labelFor = (element, index) => {
        if (element.matches('h1,h2,h3')) return element.textContent.trim().slice(0, 42) || `Título ${index + 1}`;
        if (element.matches('p')) return element.textContent.trim().slice(0, 42) || `Texto ${index + 1}`;
        if (element.matches('img')) return element.alt?.trim() || `Imagem ${index + 1}`;
        if (element.matches('article')) return `Card ${index + 1}`;
        if (element.matches('a,button')) return element.textContent.trim().slice(0, 42) || `Botão ${index + 1}`;
        return `Elemento ${index + 1}`;
    };

    const iconFor = element => {
        if (element.matches('img')) return '◩';
        if (element.matches('h1,h2,h3')) return 'T';
        if (element.matches('p')) return '¶';
        if (element.matches('a,button')) return '↗';
        if (element.matches('article')) return '▭';
        return '•';
    };

    const selectableChildren = section => [...section.querySelectorAll('h1,h2,h3,p,img,article,a,button')]
        .filter(element => !element.closest('[data-finder-builder-chrome]'))
        .filter((element, index, all) => all.indexOf(element) === index)
        .slice(0, 80);

    const buildLayerTree = panel => {
        const host = panel.querySelector('[data-layers]');
        if (!host || host.dataset.layersEnhanced === '1') return;
        host.dataset.layersEnhanced = '1';

        const toolbar = document.createElement('div');
        toolbar.className = 'fue-layer-tools';
        toolbar.innerHTML = '<input type="search" placeholder="Pesquisar elementos…" aria-label="Pesquisar elementos"><span>0</span>';
        host.before(toolbar);

        const search = toolbar.querySelector('input');
        const count = toolbar.querySelector('span');

        const render = () => {
            const query = search.value.trim().toLowerCase();
            host.innerHTML = '';
            let visible = 0;

            sections().forEach((section, sectionIndex) => {
                const type = section.dataset.builderSectionType || `Secção ${sectionIndex + 1}`;
                const header = document.createElement('button');
                header.type = 'button';
                header.className = 'fue-layer-section';
                header.innerHTML = `<span class="fue-layer-index">${sectionIndex + 1}</span><span class="fue-layer-name">${escape(type.replaceAll('_', ' '))}</span><span class="fue-layer-meta">${sectionIndex === 0 ? 'Topo' : ''}</span>`;
                header.addEventListener('click', () => {
                    section.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
                host.appendChild(header);

                const elements = selectableChildren(section);
                const group = document.createElement('div');
                group.className = 'fue-layer-children';

                elements.forEach((element, elementIndex) => {
                    const label = labelFor(element, elementIndex);
                    const haystack = `${label} ${element.tagName} ${element.className}`.toLowerCase();
                    if (query && !haystack.includes(query)) return;
                    visible += 1;

                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'fue-layer-element';
                    item.innerHTML = `<span class="fue-layer-icon">${iconFor(element)}</span><span class="fue-layer-name">${escape(label)}</span><span class="fue-layer-tag">${element.tagName.toLowerCase()}</span>`;
                    item.addEventListener('click', event => {
                        event.stopPropagation();
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        element.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, view: window, shiftKey: event.shiftKey }));
                    });
                    group.appendChild(item);
                });

                if (group.children.length) host.appendChild(group);
            });

            count.textContent = `${visible}`;
        };

        search.addEventListener('input', render);
        render();
    };

    const watch = () => {
        const panel = document.getElementById('finder-builder-canva-layers');
        if (panel) buildLayerTree(panel);
    };

    const boot = () => {
        if (!isBuilder()) return;
        watch();
        state.observer?.disconnect();
        state.observer = new MutationObserver(() => watch());
        state.observer.observe(document.body, { childList: true, subtree: true });
        if (window.Livewire) Livewire.hook('morph.updated', () => setTimeout(watch, 70));
    };

    if (window.Livewire) boot();
    else document.addEventListener('livewire:init', boot, { once: true });
})();
