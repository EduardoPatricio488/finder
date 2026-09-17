(function () {
    'use strict';

    const rootSelector = '[wire\\:id]';
    const isBuilder = () => Boolean(document.querySelector(rootSelector)) && /\/websites\/[^/]+\/builder$/.test(window.location.pathname);
    const root = () => document.querySelector(rootSelector);
    const wire = () => {
        const node = root();
        return node && window.Livewire ? window.Livewire.find(node.getAttribute('wire:id')) : null;
    };

    const groups = [
        {
            name: 'Layout',
            items: [
                ['hero', 'Hero', 'Cabeçalho principal com título, texto e CTA', '✦'],
                ['text', 'Texto', 'Título e conteúdo livre', 'T'],
                ['card', 'Card', 'Bloco de conteúdo destacado', '▣'],
                ['feature_grid', 'Grelha', 'Benefícios ou serviços em colunas', '▦'],
                ['cta', 'CTA', 'Chamada para ação', '↗'],
            ],
        },
        {
            name: 'Media',
            items: [
                ['image', 'Imagem', 'Imagem de destaque ou editorial', '◫'],
                ['gallery', 'Galeria', 'Grelha de imagens', '▧'],
                ['video', 'Vídeo', 'Conteúdo de vídeo incorporado', '▶'],
            ],
        },
        {
            name: 'Negócio',
            items: [
                ['product_grid', 'Produtos', 'Catálogo ligado aos produtos do Finder', '◈'],
                ['product_card', 'Produto', 'Destaque individual de produto', '◇'],
                ['pricing', 'Preços', 'Planos e preços', '€'],
                ['blog_posts', 'Artigos', 'Lista de artigos/notícias', '✎'],
            ],
        },
        {
            name: 'Conversão',
            items: [
                ['contact_form', 'Contacto', 'Formulário de contacto', '✉'],
                ['newsletter', 'Newsletter', 'Captação de subscritores', '⌁'],
                ['faq', 'FAQ', 'Perguntas frequentes', '?'],
                ['testimonials', 'Testemunhos', 'Prova social de clientes', '“'],
                ['social_links', 'Redes sociais', 'Links para redes sociais', '@'],
                ['map', 'Mapa', 'Localização e mapa', '⌖'],
            ],
        },
    ];

    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;' }[char]));
    const close = () => document.getElementById('finder-builder-elements-library')?.classList.add('hidden');

    const add = type => {
        const app = wire();
        if (!app) return;
        app.addSection(type);
        close();
        window.dispatchEvent(new CustomEvent('finder-canva-notify', { detail: { message: `${type === 'hero' ? 'Hero' : 'Elemento'} adicionado ao canvas` } }));
    };

    const open = () => {
        let panel = document.getElementById('finder-builder-elements-library');
        if (!panel) {
            panel = document.createElement('div');
            panel.id = 'finder-builder-elements-library';
            panel.className = 'finder-canva-pro-panel fixed left-4 top-20 z-[278] overflow-y-auto';
            document.body.appendChild(panel);
        }

        panel.innerHTML = `
            <div class="sticky top-0 z-10 border-b border-zinc-100 bg-white/95 px-4 py-4 backdrop-blur-xl">
                <div class="flex items-start justify-between gap-3">
                    <div><div class="text-[9px] font-black uppercase tracking-[.16em] text-zinc-400">Adicionar</div><div class="mt-1 text-sm font-black">Elementos do website</div><div class="mt-1 text-[10px] text-zinc-400">Começa com um bloco e personaliza tudo no canvas.</div></div>
                    <button type="button" data-close class="rounded-lg px-2 py-1 text-xl text-zinc-400 hover:bg-zinc-100">×</button>
                </div>
                <input data-search type="search" placeholder="Pesquisar elementos..." class="mt-4 w-full min-h-[38px] rounded-xl border border-zinc-200 px-3 text-xs font-bold outline-none focus:border-zinc-400">
            </div>
            <div data-content class="p-3"></div>
        `;

        panel.querySelector('[data-close]').addEventListener('click', close);
        const content = panel.querySelector('[data-content]');
        const search = panel.querySelector('[data-search]');

        const render = term => {
            const query = String(term || '').trim().toLowerCase();
            content.innerHTML = '';
            groups.forEach(group => {
                const items = group.items.filter(item => !query || item[1].toLowerCase().includes(query) || item[2].toLowerCase().includes(query));
                if (!items.length) return;
                const section = document.createElement('section');
                section.className = 'mb-5 last:mb-1';
                section.innerHTML = `<div class="mb-2 px-1 text-[9px] font-black uppercase tracking-[.14em] text-zinc-400">${escape(group.name)}</div><div class="grid grid-cols-2 gap-2" data-items></div>`;
                const grid = section.querySelector('[data-items]');
                items.forEach(([type, label, description, icon]) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'group rounded-2xl border border-zinc-200 bg-white p-3 text-left transition hover:-translate-y-0.5 hover:border-zinc-400 hover:shadow-md';
                    button.innerHTML = `<div class="flex items-center gap-2"><span class="flex h-8 w-8 items-center justify-center rounded-xl bg-zinc-950 text-xs font-black text-white">${escape(icon)}</span><span class="text-[11px] font-black text-zinc-900">${escape(label)}</span></div><div class="mt-2 text-[9px] leading-4 text-zinc-400">${escape(description)}</div>`;
                    button.addEventListener('click', () => add(type));
                    grid.appendChild(button);
                });
                content.appendChild(section);
            });
            if (!content.children.length) content.innerHTML = '<div class="rounded-2xl bg-zinc-50 p-5 text-center text-xs font-bold text-zinc-500">Nenhum elemento encontrado.</div>';
        };

        search.addEventListener('input', () => render(search.value));
        render('');
        panel.classList.remove('hidden');
        search.focus();
    };

    const addButtonToTools = () => {
        const tools = document.getElementById('finder-builder-canva-pro-tools');
        if (!tools || tools.querySelector('[data-action="elements-library"]')) return;
        const button = document.createElement('button');
        button.dataset.action = 'elements-library';
        button.textContent = '＋ Elementos';
        button.className = 'is-active';
        button.addEventListener('click', open);
        tools.insertBefore(button, tools.firstChild);
    };

    const boot = () => {
        if (!isBuilder()) return;
        addButtonToTools();
        window.Livewire?.hook('morph.updated', () => setTimeout(addButtonToTools, 50));
        setTimeout(addButtonToTools, 80);
    };

    if (window.Livewire) boot(); else document.addEventListener('livewire:init', boot, { once: true });
})();
