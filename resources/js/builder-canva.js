(function () {
    'use strict';

    const builderSelector = '[wire\\:id]';
    const sectionSelector = '[wire\\:key^="builder-section-"]';
    const state = {
        selectedElement: null,
        selectedSection: null,
        overlay: null,
        toolbar: null,
        inspector: null,
        palette: null,
        drag: null,
        resize: null,
        persistTimer: null,
        mounted: false,
        listenersReady: false,
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

    const sectionIndex = element => {
        const section = element?.closest(sectionSelector);
        return section ? sections().indexOf(section) : -1;
    };

    const sectionType = element => {
        const section = element?.closest(sectionSelector);
        if (!section) return 'text';
        const heading = section.querySelector('h1,h2,h3');
        const typeFromData = section.dataset.builderSectionType;
        if (typeFromData) return typeFromData;
        if (heading?.classList.contains('text-4xl')) return 'hero';
        return 'text';
    };

    const isEditableTarget = element => {
        if (!element) return false;
        const tag = String(element.tagName || '').toLowerCase();
        return ['h1', 'h2', 'h3', 'p', 'img', 'span'].includes(tag);
    };

    const escapeHtml = value => String(value ?? '').replace(/[&<>\"]/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '\"': '&quot;' }[character] || character));

    const inlineText = element => (element?.textContent || '').replace(/\s+/g, ' ').trim();

    const notify = (message, tone = 'neutral') => {
        let host = document.getElementById('finder-builder-canva-feedback');
        if (!host) {
            host = document.createElement('div');
            host.id = 'finder-builder-canva-feedback';
            host.className = 'pointer-events-none fixed bottom-5 right-5 z-[260] flex max-w-[calc(100vw-2rem)] flex-col gap-2';
            document.body.appendChild(host);
        }
        const item = document.createElement('div');
        const toneClass = tone === 'success'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
            : tone === 'warning'
                ? 'border-amber-200 bg-amber-50 text-amber-950'
                : 'border-zinc-200 bg-white text-zinc-900';
        item.className = `pointer-events-auto flex items-center gap-3 rounded-2xl border px-4 py-3 text-xs font-bold shadow-2xl backdrop-blur-xl ${toneClass}`;
        item.innerHTML = `<span class="flex h-7 w-7 items-center justify-center rounded-xl bg-black/5">${tone === 'success' ? '✓' : tone === 'warning' ? '!' : '✦'}</span><span>${escapeHtml(message)}</span>`;
        host.appendChild(item);
        setTimeout(() => item.remove(), 2200);
    };

    const elementField = element => {
        const section = element?.closest(sectionSelector);
        if (!section) return null;
        const index = sectionIndex(element);
        const type = section.dataset.builderSectionType || '';
        const headings = Array.from(section.querySelectorAll('h1,h2,h3')).filter(node => !node.closest('button,a'));
        const paragraphs = Array.from(section.querySelectorAll('p')).filter(node => !node.closest('button,a'));
        const articles = Array.from(section.querySelectorAll('article'));

        if (headings[0] === element) return { field: 'title', item: null, slot: 'title', index };
        if (type === 'hero' && paragraphs[0] === element) return { field: 'subtitle', item: null, slot: 'subtitle', index };
        if (type === 'hero' && paragraphs[1] === element) return { field: 'description', item: null, slot: 'description', index };
        if (paragraphs[0] === element && type !== 'hero') return { field: type === 'text' ? 'body' : 'description', item: null, slot: type === 'text' ? 'body' : 'description', index };

        const article = element.closest('article');
        if (article) {
            const itemIndex = articles.indexOf(article);
            if (itemIndex >= 0) {
                const itemHeading = article.querySelector('p.font-black, h3, h4');
                const itemParagraph = article.querySelector('p.mt-2, p:not(.font-black)');
                if (itemHeading === element) {
                    const field = type === 'faq' ? 'question' : (type === 'testimonials' ? 'name' : 'title');
                    return { field, item: itemIndex, slot: `item.${itemIndex}.${field}`, index };
                }
                if (itemParagraph === element) {
                    const field = type === 'faq' ? 'answer' : (type === 'testimonials' ? 'quote' : (type === 'pricing' ? 'price' : 'description'));
                    return { field, item: itemIndex, slot: `item.${itemIndex}.${field}`, index };
                }
            }
        }

        const buttonText = Array.from(section.querySelectorAll('span')).find(node => {
            const text = inlineText(node);
            return node !== element && text && text.length < 100 && node.closest('a,button') && (node.closest('a,button')?.classList.contains('text-white'));
        });
        if (buttonText === element) return { field: 'button_label', item: null, slot: 'button_label', index };

        return null;
    };

    const stylePath = (index, slot, key) => `sections.${index}.settings.elements.${slot}.${key}`;

    const getSectionSettings = index => {
        const app = wire();
        if (!app?.get) return {};
        try {
            return app.get(`sections.${index}.settings`) || {};
        } catch (error) {
            return {};
        }
    };

    const getElementSettings = (index, slot) => {
        const settings = getSectionSettings(index);
        return settings?.elements?.[slot] || {};
    };

    const persistElementStyle = (element, slot, key, value) => {
        const index = sectionIndex(element);
        const app = wire();
        if (!app || index < 0 || !slot) return;
        try {
            app.set(stylePath(index, slot, key), value);
        } catch (error) {
            notify('Não foi possível guardar este estilo.', 'warning');
        }
    };

    const persistSectionStyle = (section, key, value) => {
        const index = sections().indexOf(section);
        const app = wire();
        if (!app || index < 0) return;
        try {
            app.set(`sections.${index}.settings.${key}`, value);
        } catch (error) {
            notify('Não foi possível guardar este estilo.', 'warning');
        }
    };

    const getTransform = element => {
        const transform = getComputedStyle(element).transform;
        if (!transform || transform === 'none') return { x: 0, y: 0 };
        const matrix = transform.match(/matrix(3d)?\\(([^)]+)\\)/);
        if (!matrix) return { x: 0, y: 0 };
        const values = matrix[2].split(',').map(Number);
        return matrix[1] === '3d' ? { x: values[12] || 0, y: values[13] || 0 } : { x: values[4] || 0, y: values[5] || 0 };
    };

    const applyStoredElementStyles = () => {
        const app = wire();
        if (!app?.get) return;
        const data = app.get('sections');
        if (!Array.isArray(data)) return;

        sections().forEach((section, index) => {
            const elementMap = data[index]?.settings?.elements || {};
            const headings = Array.from(section.querySelectorAll('h1,h2,h3')).filter(node => !node.closest('button,a'));
            const paragraphs = Array.from(section.querySelectorAll('p')).filter(node => !node.closest('button,a'));
            const elements = [
                [headings[0], 'title'],
                [paragraphs[0], section.dataset.builderSectionType === 'hero' ? 'subtitle' : (section.dataset.builderSectionType === 'text' ? 'body' : 'description')],
                [paragraphs[1], 'description'],
            ];

            section.querySelectorAll('article').forEach((article, itemIndex) => {
                const itemHeading = article.querySelector('p.font-black, h3, h4');
                const itemParagraph = article.querySelector('p.mt-2, p:not(.font-black)');
                const type = section.dataset.builderSectionType || '';
                if (itemHeading) elements.push([itemHeading, `item.${itemIndex}.${type === 'faq' ? 'question' : (type === 'testimonials' ? 'name' : 'title')}`]);
                if (itemParagraph) elements.push([itemParagraph, `item.${itemIndex}.${type === 'faq' ? 'answer' : (type === 'testimonials' ? 'quote' : (type === 'pricing' ? 'price' : 'description'))}`]);
            });

            elements.forEach(([element, slot]) => {
                if (!element) return;
                const stored = elementMap[slot] || {};
                if (stored.x !== undefined || stored.y !== undefined) {
                    const current = getTransform(element);
                    element.style.transform = `translate3d(${stored.x ?? current.x}px, ${stored.y ?? current.y}px, 0)`;
                }
                if (stored.width) element.style.width = stored.width;
                if (stored.fontSize) element.style.fontSize = stored.fontSize;
                if (stored.color) element.style.color = stored.color;
                if (stored.opacity) element.style.opacity = stored.opacity;
                if (stored.textAlign) element.style.textAlign = stored.textAlign;
            });
        });
    };

    const ensureElementPositioning = element => {
        if (!element) return;
        const computed = getComputedStyle(element);
        if (computed.position === 'static') element.style.position = 'relative';
        element.style.zIndex = element.style.zIndex || '2';
    };

    const clearSelection = () => {
        document.querySelectorAll('.finder-canva-selected-element').forEach(node => node.classList.remove('finder-canva-selected-element'));
        document.querySelectorAll('.finder-canva-selected-section').forEach(node => node.classList.remove('finder-canva-selected-section'));
        state.selectedElement = null;
        state.selectedSection = null;
        if (state.overlay) state.overlay.classList.add('hidden');
        if (state.toolbar) state.toolbar.classList.add('hidden');
        if (state.inspector) state.inspector.classList.add('hidden');
    };

    const positionOverlay = () => {
        if (!state.overlay || !state.selectedElement || !document.body.contains(state.selectedElement)) return;
        const rect = state.selectedElement.getBoundingClientRect();
        state.overlay.style.left = `${Math.max(4, rect.left - 4)}px`;
        state.overlay.style.top = `${Math.max(4, rect.top - 4)}px`;
        state.overlay.style.width = `${rect.width + 8}px`;
        state.overlay.style.height = `${rect.height + 8}px`;

        if (state.toolbar) {
            const width = 360;
            const left = Math.min(window.innerWidth - width - 12, Math.max(12, rect.left + rect.width / 2 - width / 2));
            const top = rect.top > 85 ? rect.top - 52 : rect.bottom + 10;
            state.toolbar.style.left = `${left}px`;
            state.toolbar.style.top = `${Math.max(68, top)}px`;
        }
    };

    const updateInspector = () => {
        if (!state.inspector) return;
        state.inspector.innerHTML = '';
        if (!state.selectedElement) return;

        const meta = elementField(state.selectedElement);
        const section = state.selectedElement.closest(sectionSelector);
        const index = section ? sections().indexOf(section) : -1;
        const slot = meta?.slot || 'element';
        const stored = meta ? getElementSettings(index, slot) : {};

        const header = document.createElement('div');
        header.className = 'flex items-start justify-between gap-3 border-b border-zinc-100 px-4 py-4';
        header.innerHTML = `<div><div class="text-[9px] font-black uppercase tracking-[.16em] text-zinc-400">Inspector</div><div class="mt-1 text-sm font-black">${escapeHtml(meta?.field || state.selectedElement.tagName.toLowerCase())}</div></div><button type="button" data-close class="rounded-lg px-2 py-1 text-lg leading-none text-zinc-400 hover:bg-zinc-100">×</button>`;
        header.querySelector('[data-close]').addEventListener('click', clearSelection);
        state.inspector.appendChild(header);

        const body = document.createElement('div');
        body.className = 'space-y-4 p-4';

        if (meta) {
            body.appendChild(controlGroup('Alinhamento', [
                ['left', 'Esq.'],
                ['center', 'Centro'],
                ['right', 'Dir.'],
            ], stored.textAlign || getComputedStyle(state.selectedElement).textAlign, value => {
                state.selectedElement.style.textAlign = value;
                persistElementStyle(state.selectedElement, slot, 'textAlign', value);
            }));

            body.appendChild(controlGroup('Tamanho', [
                ['14px', 'Pequeno'],
                ['18px', 'Normal'],
                ['24px', 'Grande'],
                ['36px', 'XL'],
            ], stored.fontSize || getComputedStyle(state.selectedElement).fontSize, value => {
                state.selectedElement.style.fontSize = value;
                persistElementStyle(state.selectedElement, slot, 'fontSize', value);
            }, true));

            if (state.selectedElement.tagName.toLowerCase() !== 'img') {
                body.appendChild(colorControl('Cor do texto', stored.color || rgbToHex(getComputedStyle(state.selectedElement).color), value => {
                    state.selectedElement.style.color = value;
                    persistElementStyle(state.selectedElement, slot, 'color', value);
                }));
            }

            body.appendChild(rangeControl('Opacidade', 0.3, 1, 0.05, Number(stored.opacity || getComputedStyle(state.selectedElement).opacity || 1), value => {
                state.selectedElement.style.opacity = value;
                persistElementStyle(state.selectedElement, slot, 'opacity', String(value));
            }));

            body.appendChild(rangeControl('Largura', 120, Math.max(160, Math.min(900, Math.round(state.selectedElement.parentElement?.clientWidth || 600))), 10, state.selectedElement.clientWidth || 300, value => {
                const width = `${Math.round(value)}px`;
                state.selectedElement.style.width = width;
                persistElementStyle(state.selectedElement, slot, 'width', width);
            }));
        }

        const sectionSettings = section ? getSectionSettings(index) : {};
        body.appendChild(sectionDesignControl(section, index, sectionSettings));
        state.inspector.appendChild(body);
        state.inspector.classList.remove('hidden');
    };

    const controlGroup = (label, options, current, onChange, compact = false) => {
        const wrapper = document.createElement('div');
        const buttons = options.map(([value, text]) => `<button type="button" data-value="${escapeHtml(value)}" class="rounded-xl border px-2 py-2 text-[10px] font-black transition ${String(current) === value ? 'border-zinc-950 bg-zinc-950 text-white' : 'border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-50'}">${escapeHtml(text)}</button>`).join('');
        wrapper.innerHTML = `<div class="mb-2 text-[9px] font-black uppercase tracking-[.14em] text-zinc-400">${escapeHtml(label)}</div><div class="grid ${compact ? 'grid-cols-4' : 'grid-cols-3'} gap-1.5">${buttons}</div>`;
        wrapper.querySelectorAll('[data-value]').forEach(button => button.addEventListener('click', () => {
            onChange(button.dataset.value);
            wrapper.querySelectorAll('[data-value]').forEach(node => node.classList.toggle('bg-zinc-950', node === button));
            wrapper.querySelectorAll('[data-value]').forEach(node => node.classList.toggle('text-white', node === button));
        }));
        return wrapper;
    };

    const colorControl = (label, value, onChange) => {
        const wrapper = document.createElement('label');
        wrapper.className = 'block';
        wrapper.innerHTML = `<span class="mb-2 block text-[9px] font-black uppercase tracking-[.14em] text-zinc-400">${escapeHtml(label)}</span><div class="flex gap-2"><input type="color" value="${escapeHtml(normalizeColor(value))}" class="h-10 w-12 rounded-lg border-0 bg-transparent p-0"><input type="text" value="${escapeHtml(normalizeColor(value))}" class="min-w-0 flex-1 rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-xs font-semibold outline-none"></div>`;
        const color = wrapper.querySelector('input[type=color]');
        const text = wrapper.querySelector('input[type=text]');
        const apply = raw => {
            const normalized = normalizeColor(raw);
            color.value = normalized;
            text.value = normalized;
            onChange(normalized);
        };
        color.addEventListener('input', event => apply(event.target.value));
        text.addEventListener('change', event => apply(event.target.value));
        return wrapper;
    };

    const rangeControl = (label, min, max, step, value, onChange) => {
        const wrapper = document.createElement('label');
        wrapper.className = 'block';
        wrapper.innerHTML = `<div class="mb-2 flex items-center justify-between"><span class="text-[9px] font-black uppercase tracking-[.14em] text-zinc-400">${escapeHtml(label)}</span><output class="text-[10px] font-black text-zinc-700">${Math.round(value)}</output></div><input type="range" min="${min}" max="${max}" step="${step}" value="${value}" class="w-full accent-zinc-950">`;
        const input = wrapper.querySelector('input');
        const output = wrapper.querySelector('output');
        let timeout = null;
        input.addEventListener('input', event => {
            output.textContent = Math.round(Number(event.target.value));
            onChange(Number(event.target.value));
            clearTimeout(timeout);
            timeout = setTimeout(() => {}, 160);
        });
        return wrapper;
    };

    const sectionDesignControl = (section, index, settings) => {
        const wrapper = document.createElement('div');
        if (!section || index < 0) return wrapper;
        const padding = settings?.padding || 'lg';
        const radius = settings?.radius || 'none';
        const shadow = settings?.shadow || 'none';
        const background = settings?.background && settings.background !== 'transparent' ? settings.background : '#ffffff';
        wrapper.innerHTML = `<div class="mt-3 border-t border-zinc-100 pt-4"><div class="mb-3 text-[9px] font-black uppercase tracking-[.14em] text-zinc-400">Secção</div><div data-section-colour></div><div data-padding class="mt-4"></div><div data-radius class="mt-4"></div><div data-shadow class="mt-4"></div></div>`;
        wrapper.querySelector('[data-section-colour]').appendChild(colorControl('Fundo', background, value => {
            section.style.backgroundColor = value;
            persistSectionStyle(section, 'background', value);
        }));
        wrapper.querySelector('[data-padding]').appendChild(controlGroup('Espaçamento', [['sm', 'S'], ['md', 'M'], ['lg', 'L'], ['xl', 'XL']], padding, value => {
            const map = { sm: '3rem', md: '4.5rem', lg: '6rem', xl: '8rem' };
            section.style.paddingTop = map[value];
            section.style.paddingBottom = map[value];
            persistSectionStyle(section, 'padding', value);
        }, true));
        wrapper.querySelector('[data-radius]').appendChild(controlGroup('Arredondamento', [['none', '0'], ['sm', 'S'], ['md', 'M'], ['lg', 'L']], radius, value => {
            const map = { none: '0', sm: '0.75rem', md: '1.5rem', lg: '2rem' };
            section.style.borderRadius = map[value];
            persistSectionStyle(section, 'radius', value);
        }, true));
        wrapper.querySelector('[data-shadow]').appendChild(controlGroup('Sombra', [['none', '0'], ['sm', 'S'], ['md', 'M']], shadow, value => {
            const map = { none: 'none', sm: '0 8px 30px rgba(0,0,0,.08)', md: '0 20px 60px rgba(0,0,0,.12)' };
            section.style.boxShadow = map[value];
            persistSectionStyle(section, 'shadow', value);
        }, true));
        return wrapper;
    };

    const rgbToHex = value => {
        const match = String(value).match(/rgba?\\(([^)]+)\\)/);
        if (!match) return value;
        const parts = match[1].split(',').map(Number);
        if (parts.length < 3) return '#000000';
        return `#${parts.slice(0, 3).map(part => Math.max(0, Math.min(255, Math.round(part))).toString(16).padStart(2, '0')).join('')}`;
    };

    const normalizeColor = value => {
        const raw = String(value || '').trim();
        if (/^#[0-9a-f]{6}$/i.test(raw)) return raw.toLowerCase();
        if (/^#[0-9a-f]{3}$/i.test(raw)) return `#${raw.slice(1).split('').map(char => char + char).join('').toLowerCase()}`;
        return rgbToHex(raw) || '#000000';
    };

    const startElementDrag = event => {
        const element = state.selectedElement;
        if (!element || event.button !== 0 || event.target.closest('button,input,textarea,select,a')) return;
        if (event.target.isContentEditable && document.activeElement === event.target) return;
        ensureElementPositioning(element);
        const meta = elementField(element);
        if (!meta) return;
        event.preventDefault();
        const transform = getTransform(element);
        state.drag = {
            element,
            startX: event.clientX,
            startY: event.clientY,
            originX: transform.x,
            originY: transform.y,
            slot: meta.slot,
            index: meta.index,
        };
        element.classList.add('finder-canva-dragging');
        element.setPointerCapture?.(event.pointerId);
    };

    const moveElement = event => {
        if (!state.drag) return;
        const { element, startX, startY, originX, originY } = state.drag;
        const x = originX + event.clientX - startX;
        const y = originY + event.clientY - startY;
        element.style.transform = `translate3d(${Math.round(x)}px, ${Math.round(y)}px, 0)`;
        state.drag.x = Math.round(x);
        state.drag.y = Math.round(y);
        positionOverlay();
    };

    const finishElementDrag = () => {
        if (!state.drag) return;
        const drag = state.drag;
        drag.element.classList.remove('finder-canva-dragging');
        persistElementStyle(drag.element, drag.slot, 'x', drag.x ?? drag.originX);
        persistElementStyle(drag.element, drag.slot, 'y', drag.y ?? drag.originY);
        notify('Elemento reposicionado', 'success');
        state.drag = null;
    };

    const selectElement = (element, openEditor = false) => {
        if (!element || !isEditableTarget(element)) return;
        const section = element.closest(sectionSelector);
        if (!section) return;
        const meta = elementField(element);
        if (!meta) {
            if (openEditor) element.focus();
            return;
        }
        ensureElementPositioning(element);
        clearSelection();
        state.selectedElement = element;
        state.selectedSection = section;
        element.classList.add('finder-canva-selected-element');
        section.classList.add('finder-canva-selected-section');
        state.overlay?.classList.remove('hidden');
        state.toolbar?.classList.remove('hidden');
        updateInspector();
        positionOverlay();
        wire()?.selectSection(meta.index);
        if (openEditor) {
            element.focus();
            const range = document.createRange();
            range.selectNodeContents(element);
            const selection = window.getSelection();
            selection?.removeAllRanges();
            selection?.addRange(range);
        }
    };

    const makeEditable = element => {
        if (!element || element.dataset.finderCanvaReady === '1') return;
        if (!isEditableTarget(element)) return;
        const meta = elementField(element);
        if (!meta) return;
        element.dataset.finderCanvaReady = '1';
        element.dataset.canvaSlot = meta.slot;
        element.dataset.canvaOriginal = inlineText(element);
        element.classList.add('finder-canva-editable');
        element.setAttribute('contenteditable', 'true');
        element.setAttribute('spellcheck', 'false');
        element.addEventListener('focus', () => selectElement(element));
        element.addEventListener('dblclick', event => {
            event.stopPropagation();
            selectElement(element, true);
        });
        element.addEventListener('blur', async () => {
            const original = element.dataset.canvaOriginal || '';
            const value = inlineText(element);
            if (value === original) return;
            try {
                const app = wire();
                if (app) {
                    await app.updateInline(meta.index, meta.field, value, Number.isInteger(meta.item) ? meta.item : null);
                    element.dataset.canvaOriginal = value;
                    notify('Texto guardado', 'success');
                }
            } catch (error) {
                element.textContent = original;
                notify('Não foi possível guardar o texto.', 'warning');
            }
        });
        element.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                event.preventDefault();
                element.textContent = element.dataset.canvaOriginal || '';
                element.blur();
            }
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                element.blur();
            }
        });
    };

    const decorateCanvas = () => {
        sections().forEach(section => {
            section.dataset.builderSectionType = section.dataset.sectionType || section.getAttribute('data-section-type') || section.dataset.type || '';
            const headings = Array.from(section.querySelectorAll('h1,h2,h3')).filter(node => !node.closest('button,a'));
            const paragraphs = Array.from(section.querySelectorAll('p')).filter(node => !node.closest('button,a'));
            headings.slice(0, 3).forEach(makeEditable);
            paragraphs.slice(0, 4).forEach(makeEditable);
            section.querySelectorAll('article p, article h3, article h4').forEach(makeEditable);
            section.querySelectorAll('img').forEach(element => {
                if (element.closest('a,button')) return;
                if (element.dataset.finderCanvaImageReady === '1') return;
                element.dataset.finderCanvaImageReady = '1';
                element.classList.add('finder-canva-image');
                element.addEventListener('dblclick', event => {
                    event.stopPropagation();
                    const url = window.prompt('URL da imagem', element.getAttribute('src') || '');
                    if (url === null) return;
                    element.src = url;
                    const index = sectionIndex(element);
                    const app = wire();
                    if (app && index >= 0) app.updateInline(index, 'url', url);
                    notify('Imagem actualizada', 'success');
                });
            });
            section.addEventListener('pointerdown', event => {
                if (event.target.closest('.finder-canva-editable,.finder-canva-image,button,a')) return;
                if (event.button !== 0) return;
                selectElement(section.querySelector('h1,h2,h3,p'), false);
            });
        });
        applyStoredElementStyles();
    };

    const buildToolbar = () => {
        if (state.toolbar) return;
        const toolbar = document.createElement('div');
        toolbar.id = 'finder-builder-canva-toolbar';
        toolbar.className = 'fixed z-[255] hidden items-center gap-1 rounded-2xl border border-zinc-200 bg-white/95 p-1.5 shadow-[0_18px_45px_rgba(0,0,0,.16)] backdrop-blur-xl';
        toolbar.innerHTML = `
            <button data-action="edit" type="button" title="Editar texto">✎</button>
            <span class="mx-1 h-5 w-px bg-zinc-200"></span>
            <button data-action="left" type="button" title="Alinhar à esquerda">L</button>
            <button data-action="center" type="button" title="Centrar">C</button>
            <button data-action="right" type="button" title="Alinhar à direita">R</button>
            <span class="mx-1 h-5 w-px bg-zinc-200"></span>
            <button data-action="duplicate" type="button" title="Duplicar secção">⧉</button>
            <button data-action="section" type="button" title="Editar secção">⚙</button>
            <button data-action="delete" type="button" title="Eliminar secção">×</button>
        `;
        toolbar.querySelectorAll('[data-action]').forEach(button => button.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            const action = button.dataset.action;
            const element = state.selectedElement;
            const section = state.selectedSection;
            if (!element) return;
            if (action === 'edit') selectElement(element, true);
            if (['left', 'center', 'right'].includes(action)) {
                element.style.textAlign = action;
                const meta = elementField(element);
                if (meta) persistElementStyle(element, meta.slot, 'textAlign', action);
                updateInspector();
            }
            if (action === 'duplicate') {
                const index = sections().indexOf(section);
                if (index >= 0) {
                    wire()?.duplicateSection(index);
                    notify('Secção duplicada', 'success');
                }
            }
            if (action === 'section') updateInspector();
            if (action === 'delete') {
                const index = sections().indexOf(section);
                if (index >= 0 && window.confirm('Eliminar esta secção?')) {
                    wire()?.removeSection(index);
                    clearSelection();
                }
            }
        }));
        document.body.appendChild(toolbar);
        state.toolbar = toolbar;
    };

    const buildInspector = () => {
        if (state.inspector) return;
        const inspector = document.createElement('aside');
        inspector.id = 'finder-builder-canva-inspector';
        inspector.className = 'fixed bottom-4 right-4 z-[250] hidden w-[310px] max-h-[calc(100vh-110px)] overflow-y-auto rounded-[26px] border border-zinc-200 bg-white/96 shadow-[0_25px_80px_rgba(0,0,0,.18)] backdrop-blur-2xl';
        document.body.appendChild(inspector);
        state.inspector = inspector;
    };

    const buildOverlay = () => {
        if (state.overlay) return;
        const overlay = document.createElement('div');
        overlay.id = 'finder-builder-canva-selection';
        overlay.className = 'pointer-events-none fixed z-[245] hidden rounded-[8px] border-2 border-indigo-500 shadow-[0_0_0_1px_rgba(255,255,255,.8)]';
        document.body.appendChild(overlay);
        state.overlay = overlay;
    };

    const buildTopBar = () => {
        let bar = document.getElementById('finder-builder-canva-topbar');
        if (bar) return;
        bar = document.createElement('div');
        bar.id = 'finder-builder-canva-topbar';
        bar.className = 'fixed left-1/2 top-[78px] z-[240] flex -translate-x-1/2 items-center gap-1 rounded-2xl border border-zinc-200 bg-white/95 p-1.5 shadow-xl backdrop-blur-xl';
        bar.innerHTML = `
            <button data-tool="elements" type="button"><span>＋</span>Elementos</button>
            <button data-tool="design" type="button"><span>✦</span>Design</button>
            <button data-tool="zoom" type="button"><span>⌕</span>Zoom 100%</button>
            <button data-tool="grid" type="button"><span>▦</span>Guias</button>
        `;
        bar.querySelectorAll('[data-tool]').forEach(button => button.addEventListener('click', () => {
            const tool = button.dataset.tool;
            if (tool === 'elements') openPalette();
            if (tool === 'design' && state.selectedElement) updateInspector();
            if (tool === 'zoom') {
                const current = document.querySelector('[x-data]')?._x_dataStack?.[0]?.zoom || 100;
                const target = current >= 130 ? 60 : current + 10;
                window.dispatchEvent(new CustomEvent('finder-builder-set-zoom', { detail: target }));
                button.lastChild.textContent = `Zoom ${target}%`;
            }
            if (tool === 'grid') document.body.classList.toggle('finder-canva-grid-active');
        }));
        document.body.appendChild(bar);
    };

    const openPalette = () => {
        if (!state.palette) {
            const palette = document.createElement('div');
            palette.id = 'finder-builder-canva-palette';
            palette.className = 'fixed inset-0 z-[270] hidden items-start justify-center bg-zinc-950/25 px-4 pt-[17vh] backdrop-blur-sm';
            palette.innerHTML = `
                <div class="w-full max-w-2xl overflow-hidden rounded-[30px] border border-zinc-200 bg-white shadow-[0_35px_110px_rgba(0,0,0,.28)]">
                    <div class="flex items-center gap-3 border-b border-zinc-100 px-5 py-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-zinc-950 text-white">＋</div>
                        <div class="min-w-0 flex-1"><div class="text-sm font-black">Elementos</div><div class="text-[11px] text-zinc-400">Escolhe um bloco para adicionar ao teu website.</div></div>
                        <button data-close type="button" class="rounded-xl px-3 py-2 text-xl leading-none text-zinc-400 hover:bg-zinc-100">×</button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 p-3 sm:grid-cols-3">
                        ${[
                            ['hero', 'Hero', '✦'],
                            ['text', 'Texto', 'T'],
                            ['image', 'Imagem', '▧'],
                            ['button', 'Botão', '→'],
                            ['feature_grid', 'Benefícios', '◆'],
                            ['card', 'Card', '▣'],
                            ['testimonials', 'Testemunhos', '“'],
                            ['faq', 'FAQ', '?'],
                            ['gallery', 'Galeria', '▦'],
                            ['contact_form', 'Contacto', '@'],
                            ['product_grid', 'Produtos', '◫'],
                            ['pricing', 'Preços', '€'],
                            ['video', 'Vídeo', '▶'],
                            ['map', 'Mapa', '⌖'],
                            ['newsletter', 'Newsletter', '✉'],
                            ['cta', 'CTA', '↗'],
                        ].map(([type, label, icon]) => `<button type="button" data-add="${type}" class="group rounded-2xl border border-zinc-200 p-4 text-left transition hover:-translate-y-0.5 hover:border-zinc-400 hover:shadow-lg"><span class="flex h-9 w-9 items-center justify-center rounded-xl bg-zinc-100 text-sm font-black group-hover:bg-zinc-950 group-hover:text-white">${icon}</span><span class="mt-3 block text-xs font-black">${label}</span><span class="mt-1 block text-[10px] text-zinc-400">Bloco editável</span></button>`).join('')}
                    </div>
                </div>`;
            document.body.appendChild(palette);
            state.palette = palette;
            palette.querySelector('[data-close]').addEventListener('click', closePalette);
            palette.addEventListener('click', event => {
                const add = event.target.closest('[data-add]');
                if (!add) {
                    if (event.target === palette) closePalette();
                    return;
                }
                wire()?.addSection(add.dataset.add);
                closePalette();
                notify(`${add.textContent.trim().split(' ')[0]} adicionado`, 'success');
            });
        }
        state.palette.classList.remove('hidden');
        state.palette.classList.add('flex');
    };

    const closePalette = () => {
        if (!state.palette) return;
        state.palette.classList.add('hidden');
        state.palette.classList.remove('flex');
    };

    const bindGlobalEvents = () => {
        if (state.listenersReady) return;
        state.listenersReady = true;

        document.addEventListener('pointerdown', event => {
            if (!isBuilder()) return;
            if (event.target.closest('#finder-builder-canva-toolbar,#finder-builder-canva-inspector,#finder-builder-canva-palette,#finder-builder-canva-topbar')) return;
            const editable = event.target.closest('.finder-canva-editable,.finder-canva-image');
            if (editable) {
                selectElement(editable);
                return;
            }
            if (!event.target.closest(sectionSelector)) clearSelection();
        });

        document.addEventListener('pointermove', moveElement);
        document.addEventListener('pointerup', finishElementDrag);
        document.addEventListener('pointercancel', finishElementDrag);

        document.addEventListener('pointerdown', event => {
            if (!state.selectedElement) return;
            if (event.target.closest('.finder-canva-selected-element')) startElementDrag(event);
        }, true);

        document.addEventListener('keydown', event => {
            if (!isBuilder()) return;
            const modifier = event.ctrlKey || event.metaKey;
            if (modifier && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                openPalette();
            }
            if (event.key === 'Escape') {
                closePalette();
                clearSelection();
            }
        });

        window.addEventListener('scroll', positionOverlay, { passive: true });
        window.addEventListener('resize', positionOverlay, { passive: true });
    };

    const install = () => {
        if (!isBuilder()) return;
        buildTopBar();
        buildToolbar();
        buildInspector();
        buildOverlay();
        decorateCanvas();
        bindGlobalEvents();
        if (!state.mounted) notify('Canva Mode activo — selecciona qualquer elemento para editar.', 'success');
        state.mounted = true;
    };

    const boot = () => {
        if (!isBuilder()) return;
        install();
        if (window.Livewire) Livewire.hook('morph.updated', () => setTimeout(install, 50));
    };

    if (window.Livewire) boot();
    else document.addEventListener('livewire:init', boot, { once: true });

    const style = document.createElement('style');
    style.id = 'finder-builder-canva-styles';
    style.textContent = `
        .finder-canva-editable { cursor: text; outline: 2px dashed transparent; outline-offset: 5px; border-radius: 8px; transition: outline-color .16s ease, background-color .16s ease, box-shadow .16s ease; }
        .finder-canva-editable:hover { outline-color: rgba(99,102,241,.34); background: rgba(99,102,241,.035); }
        .finder-canva-selected-element { outline: 2px solid #6366f1 !important; outline-offset: 4px; background: rgba(99,102,241,.035); }
        .finder-canva-selected-section { scroll-margin-top: 110px; }
        .finder-canva-dragging { cursor: grabbing !important; opacity: .86; }
        .finder-canva-image { cursor: pointer; transition: outline .16s ease, filter .16s ease; }
        .finder-canva-image:hover { outline: 2px dashed rgba(99,102,241,.35); outline-offset: 4px; filter: saturate(1.02); }
        #finder-builder-canva-toolbar button { min-width: 32px; height: 32px; padding: 0 9px; border-radius: 10px; color: #52525b; font-size: 10px; font-weight: 900; transition: background-color .16s ease, color .16s ease, transform .16s ease; }
        #finder-builder-canva-toolbar button:hover { background: #f4f4f5; color: #18181b; transform: translateY(-1px); }
        #finder-builder-canva-topbar button { display: inline-flex; align-items: center; gap: 7px; border-radius: 11px; padding: 8px 11px; color: #52525b; font-size: 10px; font-weight: 900; transition: background .16s ease, color .16s ease; }
        #finder-builder-canva-topbar button:hover { background: #f4f4f5; color: #18181b; }
        #finder-builder-canva-topbar button span { color: #818cf8; font-size: 13px; }
        .finder-canva-grid-active main::before { content: ''; position: fixed; inset: 68px 0 0; pointer-events: none; z-index: 70; background-image: linear-gradient(rgba(99,102,241,.055) 1px, transparent 1px), linear-gradient(90deg, rgba(99,102,241,.055) 1px, transparent 1px); background-size: 24px 24px; }
        @media (max-width: 1023px) { #finder-builder-canva-topbar { top: auto; bottom: 86px; } #finder-builder-canva-inspector { right: 10px; left: 10px; bottom: 78px; width: auto; max-height: 58vh; } }
        @media (prefers-reduced-motion: reduce) { .finder-canva-editable, .finder-canva-image, #finder-builder-canva-toolbar button, #finder-builder-canva-topbar button { transition: none !important; } }
    `;
    document.head.appendChild(style);
})();
