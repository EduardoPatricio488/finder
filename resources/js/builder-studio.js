(function () {
    'use strict';

    const isBuilder = () => document.querySelector('[wire\\:id]') && /\/websites\/[^/]+\/builder/.test(window.location.pathname);

    if (!isBuilder()) return;

    const state = { saveTimer: null, dragged: null };

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
            host.className = 'fixed bottom-5 right-5 z-[100] flex max-w-[calc(100vw-2rem)] flex-col gap-2';
            document.body.appendChild(host);
        }
        const item = document.createElement('div');
        item.className = 'rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-xs font-bold text-zinc-800 shadow-2xl transition-all duration-200';
        if (tone === 'success') item.className += ' border-emerald-200';
        item.textContent = message;
        host.appendChild(item);
        setTimeout(() => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(8px)';
            setTimeout(() => item.remove(), 220);
        }, 2200);
    };

    const scheduleAutosave = () => {
        clearTimeout(state.saveTimer);
        state.saveTimer = setTimeout(() => {
            const app = wire();
            if (!app) return;
            if (document.body.innerText.includes('Alterações por guardar')) app.save();
        }, 12000);
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
                if (app) {
                    app.reorderSections(ids);
                    toast('Ordem das secções actualizada');
                    scheduleAutosave();
                }
            });
        });
    };

    const bindLivewireEvents = () => {
        if (!window.Livewire || window.__finderBuilderEventsBound) return;
        window.__finderBuilderEventsBound = true;
        Livewire.on('builder-dirty', scheduleAutosave);
        Livewire.on('builder-saved', () => toast('Alterações guardadas', 'success'));
        Livewire.on('builder-published', () => toast('Website publicado', 'success'));
    };

    const install = () => {
        enableSectionDrag();
        bindLivewireEvents();
    };

    document.addEventListener('keydown', event => {
        if (!isBuilder()) return;
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
            event.preventDefault();
            const app = wire();
            if (app) app.save();
        }
    });

    window.addEventListener('beforeunload', event => {
        if (!isBuilder()) return;
        if (document.body.innerText.includes('Alterações por guardar')) {
            event.preventDefault();
            event.returnValue = '';
        }
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
