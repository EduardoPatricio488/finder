(function () {
    'use strict';

    const builderSelector = '[wire\\:id]';
    const sectionSelector = '[wire\\:key^="builder-section-"]';
    const state = {
        selected: -1,
        editing: null,
        observer: null,
        mounted: false,
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
    const sectionIndex = section => sections().indexOf(section);
    const isTyping = event => {
        const target = event?.target;
        if (!target) return false;
        const tag = String(target.tagName || '').toLowerCase();
        return target.isContentEditable || ['input', 'textarea', 'select'].includes(tag);
    };

    const toast = (message, tone = 'neutral') => {
        let host = document.getElementById('finder-builder-interaction-feedback');
        if (!host) {
            host = document.createElement('div');
            host.id = 'finder-builder-interaction-feedback';
            host.className = 'pointer-events-none fixed bottom-5 right-5 z-[190] flex max-w-[calc(100vw-2rem)] flex-col gap-2';
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

        state.selected = index;
        nodes.forEach((section, current) => {
            const active = current === index;
            section.toggleAttribute('data-builder-selected', active);
            section.classList.toggle('finder-builder-section-selected', active);
        });

        wire()?.selectSection(index);

        if (scroll) {
            nodes[index].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    };

    const moveSelection = delta => {
        const nodes = sections();
        if (!nodes.length) return;
        const current = state.selected >= 0 ? state.selected : Number(wire()?.selectedSection ?? 0);
        const next = Math.max(0, Math.min(nodes.length - 1, current + delta));
        if (next !== current) selectSection(next, true);
    };

    const inlineValue = element => (element.textContent || '').replace(/\\s+/g, ' ').trim();

    const commitInline = async element => {
        const app = wire();
        const section = element.closest(sectionSelector);
        if (!app || !section) return;

        const index = sectionIndex(section);
        const field = element.dataset.finderInlineField;
        const itemValue = element.dataset.finderInlineItem;
        const itemIndex = itemValue === undefined ? null : Number(itemValue);
        if (!field || index < 0) return;

        const value = inlineValue(element);
        const original = element.dataset.finderInlineOriginal || '';
        if (value === original) return;

        try {
            await app.updateInline(index, field, value, Number.isInteger(itemIndex) ? itemIndex : null);
            element.dataset.finderInlineOriginal = value;
            toast('Alteração guardada', 'success');
        } catch (error) {
            element.textContent = original;
            toast('Não foi possível guardar esta alteração', 'warning');
        }
    };

    const makeInlineEditable = (element, field, itemIndex = null) => {
        if (!element || element.dataset.finderInlineReady === '1') return;
        if (element.closest('button,a,input,textarea,select')) return;

        element.dataset.finderInlineReady = '1';
        element.dataset.finderInlineField = field;
        if (itemIndex !== null) element.dataset.finderInlineItem = String(itemIndex);
        element.dataset.finderInlineOriginal = inlineValue(element);
        element.setAttribute('contenteditable', 'true');
        element.setAttribute('spellcheck', 'false');
        element.setAttribute('role', 'textbox');
        element.classList.add('finder-inline-editable');

        element.addEventListener('focus', () => {
            state.editing = element;
            element.dataset.finderInlineOriginal = inlineValue(element);
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
            const heading = Array.from(section.querySelectorAll('h1,h2,h3')).find(element => !element.closest('button,a'));
            const paragraph = Array.from(section.querySelectorAll('p')).find(element => !element.closest('button,a'));

            if (heading) makeInlineEditable(heading, 'title');
            if (paragraph) makeInlineEditable(paragraph, 'description');
        });
    };

    const createQuickRail = section => {
        if (section.dataset.finderQuickRailReady === '1') return;
        section.dataset.finderQuickRailReady = '1';

        const rail = document.createElement('div');
        rail.className = 'finder-builder-quick-rail';
        rail.innerHTML = '<button type="button" title="Adicionar secção" aria-label="Adicionar secção">+</button>';
        rail.firstElementChild.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            const app = wire();
            if (!app) return;
            app.addSection('text');
            toast('Nova secção adicionada', 'success');
        });
        section.appendChild(rail);
    };

    const prepareSectionSelection = () => {
        sections().forEach((section, index) => {
            if (section.dataset.finderSectionUXReady !== '1') {
                section.dataset.finderSectionUXReady = '1';
                section.addEventListener('mouseenter', () => section.setAttribute('data-builder-hover', 'true'));
                section.addEventListener('mouseleave', () => section.removeAttribute('data-builder-hover'));
                section.addEventListener('click', event => {
                    if (event.target.closest('button,a,input,textarea,select,[contenteditable="true"]')) return;
                    selectSection(index);
                });
            }

            createQuickRail(section);
        });
    };

    const buildSectionNavigator = () => {
        let navigator = document.getElementById('finder-builder-section-navigator');
        if (!navigator) {
            navigator = document.createElement('nav');
            navigator.id = 'finder-builder-section-navigator';
            navigator.className = 'finder-builder-section-navigator';
            navigator.setAttribute('aria-label', 'Navegação entre secções');
            document.body.appendChild(navigator);
        }

        navigator.innerHTML = '';
        sections().forEach((section, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.title = `Ir para secção ${index + 1}`;
            button.setAttribute('aria-label', `Ir para secção ${index + 1}`);
            button.addEventListener('click', () => selectSection(index, true));
            navigator.appendChild(button);
        });

        updateSectionNavigator();
    };

    const updateSectionNavigator = () => {
        const navigator = document.getElementById('finder-builder-section-navigator');
        if (!navigator) return;
        const nodes = sections();
        Array.from(navigator.children).forEach((button, index) => {
            const node = nodes[index];
            if (!node) return;
            const rect = node.getBoundingClientRect();
            const active = index === state.selected || (rect.top <= window.innerHeight * 0.55 && rect.bottom >= window.innerHeight * 0.25);
            button.classList.toggle('is-active', active);
        });
    };

    const installObserver = () => {
        state.observer?.disconnect();
        if (!('IntersectionObserver' in window)) return;

        state.observer = new IntersectionObserver(entries => {
            entries.forEach(entry => entry.target.classList.toggle('finder-builder-in-view', entry.isIntersecting));
            updateSectionNavigator();
        }, { threshold: [0, 0.2, 0.5] });

        sections().forEach(section => state.observer.observe(section));
    };

    const syncSelectionFromLivewire = () => {
        const index = Number(wire()?.selectedSection);
        if (Number.isInteger(index) && index >= 0) selectSection(index, false);
    };

    const install = () => {
        if (!isBuilder()) return;
        prepareInlineEditing();
        prepareSectionSelection();
        buildSectionNavigator();
        installObserver();
        syncSelectionFromLivewire();
        state.mounted = true;
    };

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
        if (!isBuilder() || isTyping(event)) return;

        const modifier = event.ctrlKey || event.metaKey;
        const key = event.key.toLowerCase();

        if (modifier && key === 'd') {
            const app = wire();
            const index = Number(app?.selectedSection);
            if (Number.isInteger(index)) {
                event.preventDefault();
                app.duplicateSection(index);
            }
            return;
        }

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

    document.addEventListener('scroll', updateSectionNavigator, { passive: true });
    window.addEventListener('resize', updateSectionNavigator, { passive: true });

    const style = document.createElement('style');
    style.id = 'finder-builder-interaction-styles';
    style.textContent = `
        .finder-inline-editable { cursor: text; outline: 2px dashed transparent; outline-offset: 5px; border-radius: 8px; transition: outline-color .16s ease, background-color .16s ease, box-shadow .16s ease; }
        .finder-inline-editable:hover { outline-color: rgba(99,102,241,.35); background-color: rgba(99,102,241,.035); }
        .finder-inline-editing { outline-color: rgba(99,102,241,.9) !important; background-color: rgba(99,102,241,.06); box-shadow: 0 0 0 5px rgba(99,102,241,.08); }
        [data-builder-selected="true"].finder-builder-section-selected { outline: 2px solid #18181b; outline-offset: -2px; box-shadow: 0 14px 45px rgba(24,24,27,.09); }
        [data-builder-selected="true"].finder-builder-section-selected::after { content: 'SECÇÃO'; position: absolute; top: 12px; left: 12px; z-index: 40; pointer-events: none; border-radius: 7px; background: #18181b; padding: 4px 7px; font-size: 8px; font-weight: 900; letter-spacing: .12em; color: #fff; }
        .finder-builder-quick-rail { position: absolute; left: 50%; bottom: -14px; z-index: 45; transform: translateX(-50%); opacity: 0; transition: opacity .18s ease, transform .18s ease; }
        [wire\\:key^="builder-section-"]:hover > .finder-builder-quick-rail, .finder-builder-quick-rail:focus-within { opacity: 1; transform: translate(-50%, -2px); }
        .finder-builder-quick-rail button { width: 29px; height: 29px; border: 1px solid #e4e4e7; border-radius: 999px; background: #fff; color: #18181b; font-size: 17px; font-weight: 900; line-height: 1; box-shadow: 0 10px 28px rgba(0,0,0,.14); cursor: pointer; }
        .finder-builder-section-navigator { position: fixed; top: 50%; right: 10px; z-index: 90; display: flex; flex-direction: column; gap: 7px; transform: translateY(-50%); padding: 8px; border: 1px solid rgba(228,228,231,.9); border-radius: 999px; background: rgba(255,255,255,.8); box-shadow: 0 14px 35px rgba(0,0,0,.08); backdrop-filter: blur(14px); }
        .finder-builder-section-navigator button { width: 7px; height: 7px; border: 0; border-radius: 999px; background: #d4d4d8; padding: 0; cursor: pointer; transition: transform .18s ease, background-color .18s ease; }
        .finder-builder-section-navigator button.is-active { background: #18181b; transform: scale(1.5); }
        @media (max-width: 1023px) { .finder-builder-section-navigator { right: 5px; } }
        @media (prefers-reduced-motion: reduce) { .finder-inline-editable, .finder-builder-quick-rail, .finder-builder-section-navigator button { transition: none !important; } }
    `;
    document.head.appendChild(style);

    const boot = () => {
        if (!isBuilder()) return;
        install();
        if (window.Livewire) {
            Livewire.hook('morph.updated', () => setTimeout(install, 40));
        }
    };

    if (window.Livewire) boot();
    else document.addEventListener('livewire:init', boot, { once: true });
})();
