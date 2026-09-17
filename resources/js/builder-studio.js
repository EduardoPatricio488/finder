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
    };

    const root = () => document.querySelector('[wire\\:id]');

    const wire = () => {
        const el = root();
        return el && window.Livewire ? window.Livewire.find(el.getAttribute('wire:id')) : null;
    };

    const toast = (message, tone = 'dark') => {
        let host = document.getElementById('finder-builder-toasts');
        if (!host) {
            host = document.createElement('div');
            host.id = 'finder-builder-toasts';
            host.className = 'pointer-events-none fixed bottom-5 right-5 z-[100] flex max-w-[calc(100vw-2rem)] flex-col gap-2';
            document.body.appendChild(host);
        }

        const item = document.createElement('div');
        item.className = 'pointer-events-auto flex items-center gap-3 rounded-2xl border bg-white/95 px-4 py-3 text-xs font-bold text-zinc-800 shadow-2xl backdrop-blur-xl transition-all duration-200';
        if (tone === 'success') item.className += ' border-emerald-200';
        if (tone === 'info') item.className += ' border-blue-200';
        item.innerHTML = `<span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg ${tone === 'success' ? 'bg-emerald-50 text-emerald-600' : 'bg-zinc-100 text-zinc-500'}">${tone === 'success' ? '✓' : 'i'}</span><span></span>`;
        item.querySelector('span:last-child').textContent = message;
        host.appendChild(item);

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
            if (reason === 'auto') toast('Alterações guardadas automaticamente', 'success');
        } finally {
            state.saving = false;
        }
    };

    const scheduleAutosave = () => {
        state.dirty = true;
        clearTimeout(state.saveTimer);
        state.saveTimer = setTimeout(() => saveNow('auto'), 5000);
    };

    const sectionNodes = () => Array.from(document.querySelectorAll('[wire\\:key^="builder-section-"]'));
    const sectionId = node => (node.getAttribute('wire:key') || '').replace('builder-section-', '');

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
            });

            section.addEventListener('dragend', () => {
                section.classList.remove('finder-builder-dragging');
                document.querySelectorAll('.finder-builder-drop-target').forEach(el => el.classList.remove('finder-builder-drop-target'));
                state.dragged = null;
            });

            section.addEventListener('dragover', event => {
                if (!state.dragged || state.dragged === section) return;
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
                section.classList.add('finder-builder-drop-target');
            });

            section.addEventListener('dragleave', () => section.classList.remove('finder-builder-drop-target'));

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
            });
        });
    };

    const bindLivewireEvents = () => {
        if (!window.Livewire || state.eventsBound) return;
        state.eventsBound = true;

        Livewire.on('builder-dirty', () => {
            scheduleAutosave();
        });

        Livewire.on('builder-saved', () => {
            state.dirty = false;
            toast('Alterações guardadas', 'success');
        });

        Livewire.on('builder-published', () => {
            state.dirty = false;
            toast('Website publicado', 'success');
        });

        Livewire.on('builder-unpublished', () => {
            state.dirty = false;
            toast('Website retirado de publicação', 'info');
        });

        Livewire.on('builder-section-selected', payload => {
            const index = Number(payload?.section ?? -1);
            const node = sectionNodes()[index];
            if (node && window.matchMedia('(max-width: 1023px)').matches) {
                setTimeout(() => node.scrollIntoView({ behavior: 'smooth', block: 'center' }), 80);
            }
        });

        Livewire.on('builder-page-selected', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    };

    const install = () => {
        enableSectionDrag();
        bindLivewireEvents();
    };

    document.addEventListener('keydown', event => {
        if (!isBuilder()) return;

        const key = event.key.toLowerCase();
        const modifier = event.ctrlKey || event.metaKey;
        const app = wire();

        if (modifier && key === 's') {
            event.preventDefault();
            if (app) app.save();
            return;
        }

        if (modifier && key === 'z' && !event.shiftKey) {
            event.preventDefault();
            if (app) app.undo();
            return;
        }

        if ((modifier && key === 'y') || (modifier && event.shiftKey && key === 'z')) {
            event.preventDefault();
            if (app) app.redo();
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') saveNow('background');
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
        [wire\\:key^="builder-section-"] { transition: outline-color .18s ease, transform .18s ease, opacity .18s ease; }
    `;
    document.head.appendChild(style);

    if (window.Livewire) {
        install();
        Livewire.hook('morph.updated', () => setTimeout(install, 40));
    } else {
        document.addEventListener('livewire:init', install, { once: true });
    }
})();
