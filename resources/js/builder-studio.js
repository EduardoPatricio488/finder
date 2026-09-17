(function () {
    'use strict';

    const isBuilder = () => document.querySelector('[wire\\:id]') && /\/websites\/[^/]+\/builder/.test(window.location.pathname);

    if (!isBuilder()) return;

    const state = {
        saveTimer: null,
        dragged: null,
        dirty: false,
        saving: false,
        eventsBound: false,
        paletteOpen: false,
        focusMode: false,
        paletteIndex: 0,
        toastHost: null,
    };

    const root = () => document.querySelector('[wire\\:id]');

    const wire = () => {
        const el = root();
        return el && window.Livewire ? window.Livewire.find(el.getAttribute('wire:id')) : null;
    };

    const isTyping = event => {
        const target = event?.target;
        if (!target) return false;
        const tag = target.tagName?.toLowerCase();
        return tag === 'input' || tag === 'textarea' || tag === 'select' || target.isContentEditable;
    };

    const toast = (message, tone = 'dark') => {
        if (!state.toastHost) {
            state.toastHost = document.createElement('div');
            state.toastHost.id = 'finder-builder-toasts';
            state.toastHost.className = 'pointer-events-none fixed bottom-5 right-5 z-[100] flex max-w-[calc(100vw-2rem)] flex-col gap-2';
            document.body.appendChild(state.toastHost);
        }

        const item = document.createElement('div');
        item.className = 'pointer-events-auto flex min-w-[240px] items-center gap-3 rounded-2xl border bg-white/95 px-4 py-3 text-xs font-bold text-zinc-800 shadow-2xl backdrop-blur-xl transition-all duration-200';
        if (tone === 'success') item.className += ' border-emerald-200';
        if (tone === 'info') item.className += ' border-blue-200';
        if (tone === 'warning') item.className += ' border-amber-200';
        item.innerHTML = `<span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-xl ${tone === 'success' ? 'bg-emerald-50 text-emerald-600' : tone === 'warning' ? 'bg-amber-50 text-amber-600' : 'bg-zinc-100 text-zinc-500'}">${tone === 'success' ? '✓' : tone === 'warning' ? '!' : 'i'}</span><span></span>`;
        item.querySelector('span:last-child').textContent = message;
        state.toastHost.appendChild(item);

        setTimeout(() => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(8px) scale(.98)';
            setTimeout(() => item.remove(), 220);
        }, 2600);
    };

    const saveNow = async (reason = 'auto') => {
        if (state.saving) return;
        const app = wire();
        if (!app || !state.dirty) return;

        state.saving = true;
        try {
            await app.save();
            state.dirty = false;
            clearTimeout(state.saveTimer);
            if (reason === 'auto') toast('Alterações guardadas automaticamente', 'success');
        } catch (error) {
            toast('Não foi possível guardar as alterações', 'warning');
            throw error;
        } finally {
            state.saving = false;
        }
    };

    const scheduleAutosave = () => {
        state.dirty = true;
        clearTimeout(state.saveTimer);
        state.saveTimer = setTimeout(() => saveNow('auto').catch(() => {}), 5000);
    };

    const sectionNodes = () => Array.from(document.querySelectorAll('[wire\\:key^="builder-section-"]'));
    const sectionId = node => (node.getAttribute('wire:key') || '').replace('builder-section-', '');

    const currentSectionIndex = () => {
        const nodes = sectionNodes();
        const selected = document.querySelector('[wire\\:key^="builder-section-"][data-builder-selected="true"]');
        return selected ? nodes.indexOf(selected) : -1;
    };

    const goToSection = index => {
        const app = wire();
        const nodes = sectionNodes();
        if (!app || index < 0 || index >= nodes.length) return;
        app.selectSection(index);
        setTimeout(() => nodes[index]?.scrollIntoView({ behavior: 'smooth', block: 'center' }), 60);
    };

    const moveSelected = direction => {
        const app = wire();
        if (!app) return;
        const index = Number(app.selectedSection);
        if (!Number.isInteger(index)) return;
        app.moveSection(index, direction);
    };

    const enableSectionDrag = () => {
        sectionNodes().forEach(section => {
            if (section.dataset.finderDragReady === '1') return;
            section.dataset.finderDragReady = '1';
            section.draggable = true;
            section.classList.add('finder-builder-draggable');

            section.addEventListener('dragstart', event => {
                state.dragged = section;
                section.classList.add('finder-builder-dragging');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', sectionId(section));
                section.setAttribute('aria-grabbed', 'true');
            });

            section.addEventListener('dragend', () => {
                section.classList.remove('finder-builder-dragging');
                section.removeAttribute('aria-grabbed');
                document.querySelectorAll('.finder-builder-drop-target').forEach(el => el.classList.remove('finder-builder-drop-target'));
                state.dragged = null;
            });

            section.addEventListener('dragover', event => {
                if (!state.dragged || state.dragged === section) return;
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
                document.querySelectorAll('.finder-builder-drop-target').forEach(el => el.classList.remove('finder-builder-drop-target'));
                section.classList.add('finder-builder-drop-target');
            });

            section.addEventListener('dragleave', event => {
                if (!section.contains(event.relatedTarget)) section.classList.remove('finder-builder-drop-target');
            });

            section.addEventListener('drop', event => {
                event.preventDefault();
                section.classList.remove('finder-builder-drop-target');
                if (!state.dragged || state.dragged === section) return;

                const all = sectionNodes();
                const from = all.indexOf(state.dragged);
                const to = all.indexOf(section);
                if (from < 0 || to < 0 || from === to) return;

                const ids = all.map(sectionId);
                const moved = ids.splice(from, 1)[0];
                ids.splice(to, 0, moved);
                const app = wire();
                if (!app) return;

                app.reorderSections(ids);
                state.dirty = true;
                scheduleAutosave();
                toast('Secção reposicionada', 'success');
            });

            section.addEventListener('click', event => {
                if (event.target.closest('button,a,input,textarea,select,[contenteditable="true"]')) return;
                const all = sectionNodes();
                const index = all.indexOf(section);
                if (index < 0) return;
                section.querySelectorAll('[data-finder-selection-ring]').forEach(el => el.removeAttribute('data-finder-selection-ring'));
                section.setAttribute('data-finder-selected', 'true');
                all.forEach(node => node.removeAttribute('data-builder-selected'));
                section.setAttribute('data-builder-selected', 'true');
                wire()?.selectSection(index);
            });
        });
    };

    const installSectionSelectionObserver = () => {
        sectionNodes().forEach((section, index) => {
            if (section.dataset.finderSelectionReady === '1') return;
            section.dataset.finderSelectionReady = '1';
            section.addEventListener('mouseenter', () => section.setAttribute('data-builder-hover', 'true'));
            section.addEventListener('mouseleave', () => section.removeAttribute('data-builder-hover'));
            if (index === Number(wire()?.selectedSection)) section.setAttribute('data-builder-selected', 'true');
        });
    };

    const paletteActions = () => [
        { id: 'save', label: 'Guardar alterações', group: 'Ficheiro', hint: 'Ctrl S', run: () => saveNow('manual').then(() => toast('Alterações guardadas', 'success')).catch(() => {}) },
        { id: 'undo', label: 'Desfazer última alteração', group: 'Editar', hint: 'Ctrl Z', run: () => wire()?.undo() },
        { id: 'redo', label: 'Refazer última alteração', group: 'Editar', hint: 'Ctrl Y', run: () => wire()?.redo() },
        { id: 'add-section', label: 'Adicionar secção', group: 'Estrutura', hint: '+', run: () => { wire()?.addSection('text'); toast('Secção de texto adicionada', 'success'); } },
        { id: 'duplicate-section', label: 'Duplicar secção seleccionada', group: 'Estrutura', hint: '⌘D', run: () => { const app = wire(); const index = Number(app?.selectedSection); if (Number.isInteger(index)) app.duplicateSection(index); } },
        { id: 'move-up', label: 'Mover secção para cima', group: 'Estrutura', hint: '↑', run: () => moveSelected('up') },
        { id: 'move-down', label: 'Mover secção para baixo', group: 'Estrutura', hint: '↓', run: () => moveSelected('down') },
        { id: 'focus', label: state.focusMode ? 'Sair do Focus Mode' : 'Entrar em Focus Mode', group: 'Visualização', hint: 'Ctrl Shift F', run: () => toggleFocusMode() },
        { id: 'preview', label: 'Abrir preview do website', group: 'Visualização', hint: 'P', run: () => { const preview = document.querySelector('[data-finder-builder-preview-link]'); if (preview) preview.click(); else toast('Preview disponível no cabeçalho', 'info'); } },
    ];

    const createPalette = () => {
        if (document.getElementById('finder-builder-command-palette')) return;

        const backdrop = document.createElement('div');
        backdrop.id = 'finder-builder-command-palette';
        backdrop.className = 'fixed inset-0 z-[120] hidden items-start justify-center bg-zinc-950/30 px-4 pt-[12vh] backdrop-blur-sm';
        backdrop.innerHTML = `
            <div class="finder-command-panel w-full max-w-2xl overflow-hidden rounded-[28px] border border-zinc-200/80 bg-white shadow-[0_30px_80px_rgba(0,0,0,.24)]">
                <div class="flex items-center gap-3 border-b border-zinc-100 px-5 py-4">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-zinc-950 text-sm text-white">⌘</div>
                    <input id="finder-command-input" type="text" autocomplete="off" placeholder="Pesquisar uma acção..." class="min-w-0 flex-1 border-0 bg-transparent text-sm font-semibold text-zinc-900 outline-none ring-0 placeholder:text-zinc-400 focus:border-0 focus:ring-0">
                    <kbd class="rounded-lg border border-zinc-200 bg-zinc-50 px-2 py-1 text-[10px] font-bold text-zinc-400">ESC</kbd>
                </div>
                <div id="finder-command-results" class="max-h-[52vh] overflow-y-auto p-2"></div>
                <div class="flex items-center justify-between border-t border-zinc-100 px-5 py-3 text-[10px] font-bold uppercase tracking-[.14em] text-zinc-400">
                    <span>Enter executar</span>
                    <span>↑ ↓ navegar</span>
                </div>
            </div>`;

        document.body.appendChild(backdrop);

        const input = backdrop.querySelector('#finder-command-input');
        input.addEventListener('input', renderPalette);
        input.addEventListener('keydown', event => {
            const actions = filteredPaletteActions();
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                state.paletteIndex = Math.min(state.paletteIndex + 1, Math.max(0, actions.length - 1));
                renderPalette();
            }
            if (event.key === 'ArrowUp') {
                event.preventDefault();
                state.paletteIndex = Math.max(0, state.paletteIndex - 1);
                renderPalette();
            }
            if (event.key === 'Enter') {
                event.preventDefault();
                actions[state.paletteIndex]?.run();
                closePalette();
            }
            if (event.key === 'Escape') {
                event.preventDefault();
                closePalette();
            }
        });

        backdrop.addEventListener('click', event => {
            if (event.target === backdrop) closePalette();
        });
    };

    const filteredPaletteActions = () => {
        const query = (document.getElementById('finder-command-input')?.value || '').trim().toLowerCase();
        return paletteActions().filter(action => !query || `${action.label} ${action.group} ${action.hint}`.toLowerCase().includes(query));
    };

    const renderPalette = () => {
        const results = document.getElementById('finder-command-results');
        if (!results) return;
        const actions = filteredPaletteActions();
        state.paletteIndex = Math.min(state.paletteIndex, Math.max(0, actions.length - 1));
        results.innerHTML = '';

        if (!actions.length) {
            results.innerHTML = '<div class="px-4 py-10 text-center text-sm font-semibold text-zinc-400">Nenhuma acção encontrada.</div>';
            return;
        }

        actions.forEach((action, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `group flex w-full items-center gap-3 rounded-2xl px-3 py-3 text-left transition ${index === state.paletteIndex ? 'bg-zinc-950 text-white' : 'text-zinc-700 hover:bg-zinc-50'}`;
            button.innerHTML = `<span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ${index === state.paletteIndex ? 'bg-white/10 text-white' : 'bg-zinc-100 text-zinc-500'}">${index < 9 ? index + 1 : '•'}</span><span class="min-w-0 flex-1"><span class="block truncate text-sm font-bold">${escapeHtml(action.label)}</span><span class="mt-0.5 block text-[11px] font-medium ${index === state.paletteIndex ? 'text-zinc-400' : 'text-zinc-400'}">${escapeHtml(action.group)}</span></span><kbd class="rounded-lg border px-2 py-1 text-[10px] font-bold ${index === state.paletteIndex ? 'border-white/10 bg-white/5 text-zinc-300' : 'border-zinc-200 bg-zinc-50 text-zinc-400'}">${escapeHtml(action.hint)}</kbd>`;
            button.addEventListener('mouseenter', () => { state.paletteIndex = index; renderPalette(); });
            button.addEventListener('click', () => { action.run(); closePalette(); });
            results.appendChild(button);
        });
    };

    const openPalette = () => {
        createPalette();
        const backdrop = document.getElementById('finder-builder-command-palette');
        const input = document.getElementById('finder-command-input');
        if (!backdrop || !input) return;
        state.paletteOpen = true;
        state.paletteIndex = 0;
        backdrop.classList.remove('hidden');
        backdrop.classList.add('flex');
        input.value = '';
        renderPalette();
        requestAnimationFrame(() => input.focus());
    };

    const closePalette = () => {
        const backdrop = document.getElementById('finder-builder-command-palette');
        if (!backdrop) return;
        state.paletteOpen = false;
        backdrop.classList.add('hidden');
        backdrop.classList.remove('flex');
    };

    const toggleFocusMode = () => {
        state.focusMode = !state.focusMode;
        document.documentElement.classList.toggle('finder-builder-focus-mode', state.focusMode);
        document.body.classList.toggle('finder-builder-focus-active', state.focusMode);
        if (state.focusMode) {
            toast('Focus Mode activo — Ctrl Shift F para sair', 'info');
        } else {
            toast('Focus Mode desactivado', 'info');
        }
    };

    const escapeHtml = value => String(value).replace(/[&<>'"]/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' })[character]);

    const bindLivewireEvents = () => {
        if (!window.Livewire || state.eventsBound) return;
        state.eventsBound = true;

        Livewire.on('builder-dirty', () => scheduleAutosave());

        Livewire.on('builder-saved', () => {
            state.dirty = false;
            clearTimeout(state.saveTimer);
            toast('Alterações guardadas', 'success');
        });

        Livewire.on('builder-published', () => {
            state.dirty = false;
            clearTimeout(state.saveTimer);
            toast('Website publicado', 'success');
        });

        Livewire.on('builder-unpublished', () => {
            state.dirty = false;
            toast('Website retirado de publicação', 'info');
        });

        Livewire.on('builder-section-selected', payload => {
            const index = Number(payload?.section ?? -1);
            const nodes = sectionNodes();
            nodes.forEach(node => node.removeAttribute('data-builder-selected'));
            nodes[index]?.setAttribute('data-builder-selected', 'true');
            if (nodes[index] && window.matchMedia('(max-width: 1023px)').matches) {
                setTimeout(() => nodes[index].scrollIntoView({ behavior: 'smooth', block: 'center' }), 80);
            }
        });

        Livewire.on('builder-page-selected', () => {
            closePalette();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        Livewire.on('builder-inline-updated', () => toast('Alteração aplicada', 'success'));
    };

    const install = () => {
        enableSectionDrag();
        installSectionSelectionObserver();
        bindLivewireEvents();
        createPalette();
    };

    document.addEventListener('keydown', event => {
        if (!isBuilder()) return;

        const key = event.key.toLowerCase();
        const modifier = event.ctrlKey || event.metaKey;
        const typing = isTyping(event);

        if (modifier && key === 'k' && !typing) {
            event.preventDefault();
            openPalette();
            return;
        }

        if (event.key === 'Escape') {
            if (state.paletteOpen) {
                event.preventDefault();
                closePalette();
                return;
            }
            if (state.focusMode) {
                toggleFocusMode();
                return;
            }
        }

        if (modifier && event.shiftKey && key === 'f' && !typing) {
            event.preventDefault();
            toggleFocusMode();
            return;
        }

        const app = wire();
        if (!app || typing) return;

        if (modifier && key === 's') {
            event.preventDefault();
            saveNow('manual').catch(() => {});
            return;
        }

        if (modifier && key === 'z' && !event.shiftKey) {
            event.preventDefault();
            app.undo();
            return;
        }

        if ((modifier && key === 'y') || (modifier && event.shiftKey && key === 'z')) {
            event.preventDefault();
            app.redo();
            return;
        }

        if (key === 'arrowup' && event.shiftKey) {
            event.preventDefault();
            moveSelected('up');
            return;
        }

        if (key === 'arrowdown' && event.shiftKey) {
            event.preventDefault();
            moveSelected('down');
            return;
        }

        if (modifier && key === 'd') {
            event.preventDefault();
            const index = Number(app.selectedSection);
            if (Number.isInteger(index)) app.duplicateSection(index);
            return;
        }

        if (key === 'p') {
            const preview = document.querySelector('[data-finder-builder-preview-link]');
            if (preview) {
                event.preventDefault();
                preview.click();
            }
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') saveNow('background').catch(() => {});
    });

    window.addEventListener('beforeunload', event => {
        if (!isBuilder() || !state.dirty) return;
        event.preventDefault();
        event.returnValue = '';
    });

    const style = document.createElement('style');
    style.textContent = `
        .finder-builder-draggable { cursor: grab; }
        .finder-builder-draggable:active { cursor: grabbing; }
        .finder-builder-dragging { opacity: .42; transform: scale(.995); }
        .finder-builder-drop-target { outline: 2px dashed #18181b !important; outline-offset: -4px; background: rgba(24,24,27,.025) !important; }
        .finder-builder-draggable[data-builder-hover="true"] { box-shadow: inset 0 0 0 1px rgba(24,24,27,.10); }
        .finder-builder-draggable[data-builder-selected="true"] { box-shadow: inset 0 0 0 2px rgba(99,102,241,.55), 0 0 0 4px rgba(99,102,241,.06); }
        .finder-builder-draggable [contenteditable="true"] { outline: none; }
        .finder-command-panel { animation: finder-command-in 160ms cubic-bezier(.22,1,.36,1) both; }
        @keyframes finder-command-in { from { opacity: 0; transform: translateY(-8px) scale(.985); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .finder-builder-focus-active [data-finder-builder-sidebar],
        .finder-builder-focus-active [data-finder-builder-toolbar] { opacity: .18 !important; transition: opacity .2s ease; }
        .finder-builder-focus-active [data-finder-builder-sidebar]:hover,
        .finder-builder-focus-active [data-finder-builder-toolbar]:hover { opacity: 1 !important; }
        [wire\\:key^="builder-section-"] { transition: outline-color .18s ease, transform .18s ease, opacity .18s ease, box-shadow .18s ease; }
    `;
    document.head.appendChild(style);

    if (window.Livewire) {
        install();
        Livewire.hook('morph.updated', () => setTimeout(install, 40));
    } else {
        document.addEventListener('livewire:init', install, { once: true });
    }
})();
