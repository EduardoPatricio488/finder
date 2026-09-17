(function () {
    'use strict';

    const builderSelector = '[wire\\:id]';
    const sectionSelector = '[wire\\:key^="builder-section-"]';
    const state = {
        ready: false,
        selected: -1,
        editing: null,
        saveTimer: null,
        palette: null,
        paletteQuery: '',
        paletteIndex: 0,
        observer: null,
    };

    const isBuilder = () => document.querySelector(builderSelector) && /\\/websites\\/[^/]+\\/builder/.test(window.location.pathname);
    const root = () => document.querySelector(builderSelector);
    const wire = () => {
        const element = root();
        return element && window.Livewire ? window.Livewire.find(element.getAttribute('wire:id')) : null;
    };
    const sections = () => Array.from(document.querySelectorAll(sectionSelector));
    const sectionIndex = node => sections().indexOf(node);

    const isTyping = event => {
        const target = event?.target;
        if (!target) return false;
        const tag = String(target.tagName || '').toLowerCase();
        return target.isContentEditable || tag === 'input' || tag === 'textarea' || tag === 'select';
    };

    const toast = (message, tone = 'neutral') => {
        let host = document.getElementById('finder-builder-live-feedback');
        if (!host) {
            host = document.createElement('div');
            host.id = 'finder-builder-live-feedback';
            host.className = 'pointer-events-none fixed bottom-5 right-5 z-[180] flex max-w-[calc(100vw-2rem)] flex-col gap-2';
            document.body.appendChild(host);
        }

        const item = document.createElement('div');
        const toneClass = tone === 'success'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
            : tone === 'warning'
                ? 'border-amber-200 bg-amber-50 text-amber-900'
                : 'border-zinc-200 bg-white text-zinc-800';
        item.className = `pointer-events-auto flex items-center gap-3 rounded-2xl border px-4 py-3 text-xs font-bold shadow-2xl backdrop-blur-xl transition-all ${toneClass}`;
        item.innerHTML = `<span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-black/5 text-sm">${tone === 'success' ? '✓' : tone === 'warning' ? '!' : 'i'}</span><span></span>`;
        item.lastElementChild.textContent = message;
        host.appendChild(item);
        requestAnimationFrame(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        });

        setTimeout(() => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(8px)';
            setTimeout(() => item.remove(), 180);
        }, 2200);
    };

    const selectSection = (index, scroll = false) => {
        const nodes = sections();
        if (index < 0 || index >= nodes.length) return;
        const node = nodes[index];
        state.selected = index;
        nodes.forEach((section, current) => {
            section.toggleAttribute('data-builder-selected', current === index);
            section.classList.toggle('finder-builder-section-selected', current === index);
        });
        wire()?.selectSection(index);
        if (scroll) node.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    const moveSelection = delta => {
        const nodes = sections();
        if (!nodes.length) return;
        const current = state.selected >= 0 ? state.selected : Number(wire()?.selectedSection ?? 0);
        const next = Math.min(nodes.length - 1, Math.max(0, current + delta));
        if (next !== current) selectSection(next, true);
    };

    const escapeHtml = value => String(value).replace(/[&<>\"]/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '\"': '&quot;' }[character] || character));

    const getInlineValue = element => (element.textContent || '').replace(/\\s+/g, ' ').trim();

    const commitInline = async element => {
        const app = wire();
        const section = element.closest(sectionSelector);
        if (!app || !section) return;
        const index = sectionIndex(section);
        const field = element.dataset.finderInlineField;
        const item = element.dataset.finderInlineItem === undefined ? null : Number(element.dataset.finderInlineItem);
        if (!field || !Number.isInteger(index)) return;

        const value = getInlineValue(element);
        const original = element.dataset.finderInlineOriginal || '';
        if (value === original) return;
        try {
            await app.updateInline(index, field, value, Number.isInteger(item) ? item : null);
            element.dataset.finderInlineOriginal = value;
            toast('Alteração guardada', 'success');
        } catch (error) {
            element.textContent = original;
            toast('Não foi possível guardar esta alteração', 'warning');
        }
    };

    const prepareInlineElement = (element, field, itemIndex = null) => {
        if (!element || element.dataset.finderInlineReady === '1') return;
        if (element.closest('button,a,input,textarea,select')) return;
        element.dataset.finderInlineReady = '1';
        element.dataset.finderInlineField = field;
        if (itemIndex !== null) element.dataset.finderInlineItem = String(itemIndex);
        element.dataset.finderInlineOriginal = getInlineValue(element);
        element.setAttribute('contenteditable', 'true');
        element.setAttribute('spellcheck', 'false');
        element.setAttribute('role', 'textbox');
        element.setAttribute('aria-label', `Editar ${field}`);
        element.classList.add('finder-inline-editable');

        element.addEventListener('focus', () => {
            state.editing = element;
            element.dataset.finderInlineOriginal = getInlineValue(element);
            element.classList.add('finder-inline-editing');
        });
        element.addEventListener('blur', () => {
            element.classList.remove('finder-inline-editing');
            state.editing = null;
            commitInline(element);
        });
        element.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                event.preventDefault();
                element.textContent = element.dataset.finderInlineOriginal || '';
                element.blur();
                return;
            }
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                element.blur();
            }
        });
    };

    const prepareInlineEditing = () => {
        sections().forEach(section => {
            const headings = Array.from(section.querySelectorAll('h1,h2,h3')).filter(element => !element.closest('button,a'));
            const paragraphs = Array.from(section.querySelectorAll('p')).filter(element => !element.closest('button,a'));

            if (headings[0]) prepareInlineElement(headings[0], 'title');
            if (paragraphs[0]) prepareInlineElement(paragraphs[0], 'description');

            const labels = Array.from(section.querySelectorAll('span')).filter(element => {
                const text = getInlineValue(element);
                return text && text.length < 80 && (element.closest('button') || element.classList.contains('font-black'));
            });
            if (labels[0] && !labels[0].dataset.finderInlineReady) {
                prepareInlineElement(labels[0], 'button_label');
            }
        });
    };

    const addQuickInsertRails = () => {
        sections().forEach((section, index) => {
            if (section.dataset.finderQuickRailReady === '1') return;
            section.dataset.finderQuickRailReady = '1';

            const rail = document.createElement('div');
            rail.className = 'finder-builder-quick-rail';
            rail.innerHTML = `
                <button type="button" data-builder-add-after="${index}" title="Adicionar secção aqui" aria-label="Adicionar secção aqui">+</button>
            `;
            rail.addEventListener('click', event => {
                const button = event.target.closest('[data-builder-add-after]');
                if (!button) return;
                const app = wire();
                if (!app) return;
                const current = Number(button.dataset.builderAddAfter);
                const nodes = sections();
                if (current >= nodes.length - 1) {
                    app.addSection('text');
                    toast('Nova secção adicionada no final', 'success');
                    return;
                }
                app.selectSection(current);
                app.addSection('text');
                toast('Nova secção adicionada', 'success');
            });
            section.appendChild(rail);
        });
    };

    const addSectionBadges = () => {
        sections().forEach(section => {
            if (section.dataset.finderBadgeReady === '1') return;
            section.dataset.finderBadgeReady = '1';
            const toolbar = section.querySelector('div.absolute.right-3.top-3');
            if (!toolbar) return;
            const badge = document.createElement('span');
            badge.className = 'finder-builder-selection-badge pointer-events-none absolute -left-1 top-1/2 -translate-x-full -translate-y-1/2 rounded-lg bg-zinc-950 px-2 py-1 text-[9px] font-black uppercase tracking-[.12em] text-white opacity-0 shadow-lg transition-opacity';
            badge.textContent = 'Secção';
            toolbar.parentElement.appendChild(badge);
            section.addEventListener('mouseenter', () => badge.style.opacity = '1');
            section.addEventListener('mouseleave', () => badge.style.opacity = '0');
        });
    };

    const buildProgressRail = () => {
        let rail = document.getElementById('finder-builder-progress-rail');
        if (!rail) {
            rail = document.createElement('div');
            rail.id = 'finder-builder-progress-rail';
            rail.className = 'finder-builder-progress-rail';
            document.body.appendChild(rail);
        }

        const nodes = sections();
        rail.innerHTML = '';
        nodes.forEach((section, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.index = String(index);
            button.title = `Secção ${index + 1}`;
            button.setAttribute('aria-label', `Ir para a secção ${index + 1}`);
            button.addEventListener('click', () => selectSection(index, true));
            rail.appendChild(button);
        });
        updateProgressRail();
    };

    const updateProgressRail = () => {
        const rail = document.getElementById('finder-builder-progress-rail');
        if (!rail) return;
        const nodes = sections();
        const buttons = Array.from(rail.children);
        buttons.forEach((button, index) => {
            const node = nodes[index];
            const rect = node?.getBoundingClientRect();
            const active = index === state.selected || (rect && rect.top < window.innerHeight * 0.55 && rect.bottom > window.innerHeight * 0.25);
            button.classList.toggle('is-active', Boolean(active));
        });
    };

    const bindIntersectionObserver = () => {
        if (state.observer) state.observer.disconnect();
        state.observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                entry.target.classList.toggle('finder-builder-in-view', entry.isIntersecting);
            });
            updateProgressRail();
        }, { threshold: [0, 0.18, 0.5] });
        sections().forEach(section => state.observer.observe(section));
    };

    const paletteActions = () => {
        const app = wire();
        return [
            { label: 'Guardar alterações', hint: 'Ctrl S', run: () => app?.save() },
            { label: 'Desfazer', hint: 'Ctrl Z', run: () => app?.undo() },
            { label: 'Refazer', hint: 'Ctrl Y', run: () => app?.redo() },
            { label: 'Adicionar secção', hint: '+', run: () => app?.addSection('text') },
            { label: 'Duplicar secção', hint: 'Ctrl D', run: () => { const index = Number(app?.selectedSection); if (Number.isInteger(index)) app?.duplicateSection(index); } },
            { label: 'Mover secção para cima', hint: '↑', run: () => { const index = Number(app?.selectedSection); if (Number.isInteger(index)) app?.moveSection(index, 'up'); } },
            { label: 'Mover secção para baixo', hint: '↓', run: () => { const index = Number(app?.selectedSection); if (Number.isInteger(index)) app?.moveSection(index, 'down'); } },
            { label: 'Ir para o início', hint: 'Home', run: () => window.scrollTo({ top: 0, behavior: 'smooth' }) },
            { label: 'Ir para o fim', hint: 'End', run: () => window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' }) },
        ];
    };

    const filteredPaletteActions = () => {
        const query = state.paletteQuery.trim().toLowerCase();
        return paletteActions().filter(action => !query || `${action.label} ${action.hint}`.toLowerCase().includes(query));
    };

    const closePalette = () => {
        const palette = state.palette;
        if (!palette) return;
        palette.classList.add('hidden');
        state.paletteQuery = '';
        state.paletteIndex = 0;
    };

    const renderPalette = () => {
        const palette = state.palette;
        if (!palette) return;
        const results = palette.querySelector('[data-results]');
        const actions = filteredPaletteActions();
        state.paletteIndex = Math.min(state.paletteIndex, Math.max(0, actions.length - 1));
        results.innerHTML = '';
        if (!actions.length) {
            results.innerHTML = '<div class="px-4 py-10 text-center text-xs font-bold text-zinc-400">Nenhuma acção encontrada.</div>';
            return;
        }
        actions.forEach((action, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `flex w-full items-center gap-3 rounded-2xl px-3 py-3 text-left transition ${index === state.paletteIndex ? 'bg-zinc-950 text-white' : 'text-zinc-800 hover:bg-zinc-100'}`;
            button.innerHTML = `<span class="flex h-9 w-9 items-center justify-center rounded-xl bg-black/5 text-xs font-black">${index + 1}</span><span class="min-w-0 flex-1 truncate text-sm font-bold">${escapeHtml(action.label)}</span><kbd class="rounded-lg border border-current/10 bg-black/5 px-2 py-1 text-[9px] font-black opacity-60">${escapeHtml(action.hint)}</kbd>`;
            button.addEventListener('mouseenter', () => { state.paletteIndex = index; renderPalette(); });
            button.addEventListener('click', () => { closePalette(); action.run(); });
            results.appendChild(button);
        });
    };

    const createPalette = () => {
        if (state.palette) return;
        const palette = document.createElement('div');
        palette.id = 'finder-builder-dynamic-palette';
        palette.className = 'fixed inset-0 z-[220] hidden items-start justify-center bg-zinc-950/30 px-4 pt-[12vh] backdrop-blur-sm';
        palette.innerHTML = `
            <div class="w-full max-w-xl overflow-hidden rounded-[28px] border border-zinc-200 bg-white shadow-[0_35px_110px_rgba(0,0,0,.28)]">
                <div class="flex items-center gap-3 border-b border-zinc-100 px-5 py-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-zinc-950 text-white">⌘</div>
                    <input data-query type="search" autocomplete="off" placeholder="O que queres fazer?" class="min-w-0 flex-1 border-0 bg-transparent text-sm font-bold outline-none ring-0 focus:border-0 focus:ring-0">
                    <kbd class="rounded-lg bg-zinc-100 px-2 py-1 text-[9px] font-black text-zinc-400">ESC</kbd>
                </div>
                <div data-results class="max-h-[55vh] overflow-y-auto p-2"></div>
                <div class="flex justify-between border-t border-zinc-100 px-5 py-3 text-[9px] font-black uppercase tracking-[.16em] text-zinc-400"><span>Enter executar</span><span>↑ ↓ navegar</span></div>
            </div>`;
        document.body.appendChild(palette);
        state.palette = palette;
        const input = palette.querySelector('[data-query]');
        input.addEventListener('input', () => { state.paletteQuery = input.value; state.paletteIndex = 0; renderPalette(); });
        input.addEventListener('keydown', event => {
            const actions = filteredPaletteActions();
            if (event.key === 'ArrowDown') { event.preventDefault(); state.paletteIndex = Math.min(state.paletteIndex + 1, actions.length - 1); renderPalette(); }
            if (event.key === 'ArrowUp') { event.preventDefault(); state.paletteIndex = Math.max(0, state.paletteIndex - 1); renderPalette(); }
            if (event.key === 'Enter') { event.preventDefault(); actions[state.paletteIndex]?.run(); closePalette(); }
            if (event.key === 'Escape') { event.preventDefault(); closePalette(); }
        });
        palette.addEventListener('click', event => { if (event.target === palette) closePalette(); });
    };

    const openPalette = () => {
        createPalette();
        const input = state.palette.querySelector('[data-query]');
        state.palette.classList.remove('hidden');
        state.palette.classList.add('flex');
        state.paletteQuery = '';
        state.paletteIndex = 0;
        input.value = '';
        renderPalette();
        requestAnimationFrame(() => input.focus());
    };

    const syncSelectedFromLivewire = () => {
        const app = wire();
        const index = Number(app?.selectedSection);
        if (Number.isInteger(index) && index >= 0) selectSection(index, false);
    };

    const install = () => {
        if (!isBuilder()) return;
        prepareInlineEditing();
        addQuickInsertRails();
        addSectionBadges();
        buildProgressRail();
        bindIntersectionObserver();
        syncSelectedFromLivewire();
        state.ready = true;
    };

    document.addEventListener('click', event => {
        if (!isBuilder()) return;
        if (event.target.closest('#finder-builder-dynamic-palette')) return;
        if (event.target.closest('button,a,input,textarea,select,[contenteditable="true"]')) return;
        const section = event.target.closest(sectionSelector);
        if (section) selectSection(sectionIndex(section));
    });

    document.addEventListener('dblclick', event => {
        if (!isBuilder()) return;
        const element = event.target.closest('.finder-inline-editable');
        if (!element) return;
        event.preventDefault();
        element.focus();
        const selection = window.getSelection();
        const range = document.createRange();
        range.selectNodeContents(element);
        selection?.removeAllRanges();
        selection?.addRange(range);
    });

    document.addEventListener('keydown', event => {
        if (!isBuilder()) return;
        const modifier = event.ctrlKey || event.metaKey;
        const key = event.key.toLowerCase();

        if (modifier && key === 'k' && !isTyping(event)) {
            event.preventDefault();
            openPalette();
            return;
        }
        if (modifier && key === 'd' && !isTyping(event)) {
            const app = wire();
            const index = Number(app?.selectedSection);
            if (Number.isInteger(index)) {
                event.preventDefault();
                app.duplicateSection(index);
            }
            return;
        }
        if (event.key === 'Escape') {
            if (state.palette && !state.palette.classList.contains('hidden')) closePalette();
            return;
        }
        if (isTyping(event)) return;
        if (event.key === 'ArrowDown' || event.key === 'ArrowRight') {
            event.preventDefault();
            moveSelection(1);
        }
        if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') {
            event.preventDefault();
            moveSelection(-1);
        }
        if (event.key === 'Home') {
            event.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        if (event.key === 'End') {
            event.preventDefault();
            window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' });
        }
    });

    window.addEventListener('scroll', () => updateProgressRail(), { passive: true });
    window.addEventListener('resize', () => updateProgressRail(), { passive: true });

    const style = document.createElement('style');
    style.id = 'finder-builder-interaction-styles';
    style.textContent = `
        .finder-inline-editable { cursor: text; outline: 2px dashed transparent; outline-offset: 5px; border-radius: 8px; transition: outline-color .16s ease, background .16s ease, box-shadow .16s ease; }
        .finder-inline-editable:hover { outline-color: rgba(99,102,241,.35); background: rgba(99,102,241,.035); }
        .finder-inline-editing { outline-color: rgba(99,102,241,.85) !important; background: rgba(99,102,241,.06); box-shadow: 0 0 0 5px rgba(99,102,241,.08); }
        [data-builder-selected="true"].finder-builder-section-selected { outline: 2px solid rgba(24,24,27,.95); outline-offset: -2px; box-shadow: 0 12px 40px rgba(24,24,27,.08); }
        [data-builder-selected="true"].finder-builder-section-selected::after { content: 'SELECTED'; position: absolute; left: 12px; top: 12px; border-radius: 7px; background: #18181b; padding: 4px 7px; font-size: 8px; font-weight: 900; letter-spacing: .12em; color: white; pointer-events: none; opacity: .9; }
        .finder-builder-quick-rail { position: absolute; left: 50%; bottom: -13px; z-index: 45; transform: translateX(-50%); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity .18s ease, transform .18s ease; }
        [wire\\:key^="builder-section-"]:hover > .finder-builder-quick-rail, .finder-builder-quick-rail:focus-within { opacity: 1; transform: translate(-50%, -2px); }
        .finder-builder-quick-rail button { width: 28px; height: 28px; border: 1px solid #e4e4e7; border-radius: 999px; background: #fff; color: #18181b; font-size: 16px; font-weight: 900; line-height: 1; box-shadow: 0 8px 25px rgba(0,0,0,.12); cursor: pointer; }
        .finder-builder-progress-rail { position: fixed; top: 50%; right: 10px; z-index: 95; display: flex; transform: translateY(-50%); flex-direction: column; gap: 7px; padding: 8px; border: 1px solid rgba(228,228,231,.9); border-radius: 999px; background: rgba(255,255,255,.78); box-shadow: 0 14px 35px rgba(0,0,0,.08); backdrop-filter: blur(14px); }
        .finder-builder-progress-rail button { width: 7px; height: 7px; border: 0; border-radius: 999px; background: #d4d4d8; padding: 0; cursor: pointer; transition: transform .18s ease, background .18s ease; }
        .finder-builder-progress-rail button.is-active { background: #18181b; transform: scale(1.5); }
        .finder-builder-in-view { transform: translateY(0); }
        @media (max-width: 1023px) { .finder-builder-progress-rail { right: 5px; } }
        @media (prefers-reduced-motion: reduce) { .finder-builder-quick-rail, .finder-inline-editable, .finder-builder-progress-rail button { transition: none !important; } }
    `;
    document.head.appendChild(style);

    const boot = () => {
        if (!isBuilder()) return;
        install();
        if (window.Livewire) {
            Livewire.hook('morph.updated', () => setTimeout(install, 40));
        }
        window.addEventListener('builder-section-selected', syncSelectedFromLivewire);
    };

    if (window.Livewire) boot();
    else document.addEventListener('livewire:init', boot, { once: true });
})();
