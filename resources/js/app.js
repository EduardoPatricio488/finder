// Smooth navigation feedback for Livewire Navigate without interfering with
// Livewire form submissions or other normal browser events.
document.addEventListener('livewire:navigate', () => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    document.documentElement.classList.add('finder-navigating');
});

document.addEventListener('livewire:navigated', () => {
    document.documentElement.classList.remove('finder-navigating');
    window.finderBuilder?.boot();
});

(() => {
    let saveTimer = null;
    let saving = false;

    const getBuilderComponent = () => {
        const root = document.querySelector('[wire\\:id]');
        if (!root || !window.Livewire?.find) return null;

        return window.Livewire.find(root.getAttribute('wire:id'));
    };

    const saveBuilder = async () => {
        const component = getBuilderComponent();
        if (!component || saving) return;

        saving = true;
        try {
            await component.save();
        } finally {
            saving = false;
        }
    };

    const scheduleSave = () => {
        window.clearTimeout(saveTimer);
        saveTimer = window.setTimeout(saveBuilder, 1400);
    };

    const selectVisualElement = (event) => {
        const target = event.target instanceof Element ? event.target : null;
        if (!target) return;

        const section = target.closest('section[data-section-id]');
        if (!section) return;

        // Do not hijack real controls or an element that is being edited.
        if (target.closest('button, a, input, textarea, select, [contenteditable="true"]')) return;

        const sections = [...document.querySelectorAll('section[data-section-id]')];
        const sectionIndex = sections.indexOf(section);
        if (sectionIndex < 0) return;

        const component = getBuilderComponent();
        if (!component) return;

        event.stopPropagation();
        component.selectSection(sectionIndex);
    };

    const inlineDefinition = (section, element, field, itemIndex = null) => {
        element.dataset.finderInlineField = field;
        if (itemIndex !== null) element.dataset.finderInlineItem = String(itemIndex);
        element.contentEditable = 'true';
        element.spellcheck = true;
        element.classList.add('cursor-text', 'rounded-lg', 'outline-none', 'transition');
        element.classList.add('hover:ring-2', 'hover:ring-indigo-200', 'focus:ring-2', 'focus:ring-indigo-400', 'focus:bg-indigo-50/30');
        element.title = 'Clique para editar directamente';

        if (element.dataset.finderInlineReady === '1') return;
        element.dataset.finderInlineReady = '1';

        element.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        element.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                element.blur();
            }
        });

        element.addEventListener('blur', async () => {
            const component = getBuilderComponent();
            if (!component) return;

            const sections = [...document.querySelectorAll('section[data-section-id]')];
            const sectionIndex = sections.indexOf(section);
            if (sectionIndex < 0) return;

            const value = element.innerText.replace(/\u00a0/g, ' ').trim();
            const currentItemIndex = element.dataset.finderInlineItem;
            const item = currentItemIndex === undefined ? null : Number(currentItemIndex);

            await component.updateInline(sectionIndex, field, value, Number.isInteger(item) ? item : null);
            scheduleSave();
        });
    };

    const makeInlineEditable = (section, index) => {
        const type = section.dataset.sectionType || '';
        const content = section.querySelector(':scope > div:last-child');
        if (!content) return;

        const headings = [...content.querySelectorAll('h1, h2')];
        const paragraphs = [...content.querySelectorAll('p')];
        const buttons = [...content.querySelectorAll('span')].filter((element) => {
            return element.classList.contains('text-white') && element.classList.contains('font-black');
        });

        if (type === 'hero') {
            if (paragraphs[0]) inlineDefinition(section, paragraphs[0], 'subtitle');
            if (headings[0]) inlineDefinition(section, headings[0], 'title');
            if (paragraphs[1]) inlineDefinition(section, paragraphs[1], 'description');
            if (buttons[0]) inlineDefinition(section, buttons[0], 'button_label');
            return;
        }

        if (type === 'text') {
            if (headings[0]) inlineDefinition(section, headings[0], 'title');
            if (paragraphs[0]) inlineDefinition(section, paragraphs[0], 'body');
            return;
        }

        if (type === 'cta') {
            if (headings[0]) inlineDefinition(section, headings[0], 'title');
            if (paragraphs[0]) inlineDefinition(section, paragraphs[0], 'description');
            if (buttons[0]) inlineDefinition(section, buttons[0], 'button_label');
            return;
        }

        if (headings[0]) inlineDefinition(section, headings[0], 'title');

        const description = paragraphs[0];
        if (description && !description.closest('article')) {
            inlineDefinition(section, description, 'description');
        }

        [...content.querySelectorAll('article')].forEach((article, itemIndex) => {
            const itemHeading = article.querySelector('p.font-black');
            const itemDescription = article.querySelector('p.mt-2');

            if (itemHeading) {
                const field = type === 'faq' ? 'question' : (type === 'testimonials' ? 'name' : 'title');
                inlineDefinition(section, itemHeading, field, itemIndex);
            }

            if (itemDescription) {
                const field = type === 'faq' ? 'answer' : (type === 'testimonials' ? 'quote' : (type === 'pricing' ? 'price' : 'description'));
                inlineDefinition(section, itemDescription, field, itemIndex);
            }
        });
    };

    const bootInlineEditing = () => {
        const sections = [...document.querySelectorAll('section[data-section-id]')];
        if (!sections.length) return;

        sections.forEach((section, index) => {
            if (!section.dataset.finderInlineSectionReady) {
                section.dataset.finderInlineSectionReady = '1';
            }

            makeInlineEditable(section, index);
        });
    };

    const bootVisualSelection = () => {
        const canvas = document.querySelector('section[data-section-id]')?.closest('main');
        if (!canvas || canvas.dataset.finderVisualSelectionReady === '1') return;

        canvas.dataset.finderVisualSelectionReady = '1';
        canvas.addEventListener('click', selectVisualElement, true);
    };

    const bootDragAndDrop = () => {
        const sections = [...document.querySelectorAll('section[data-section-id][wire\\:key]')];
        if (!sections.length) return;

        sections.forEach((section) => {
            if (section.dataset.finderDragReady === '1') return;

            section.dataset.finderDragReady = '1';
            section.draggable = true;

            section.addEventListener('dragstart', (event) => {
                if (event.target instanceof Element && event.target.closest('[contenteditable="true"], input, textarea, button, a')) {
                    event.preventDefault();
                    return;
                }

                section.classList.add('opacity-60');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', section.getAttribute('wire:key') || '');
            });

            section.addEventListener('dragend', () => {
                section.classList.remove('opacity-60');
                document.querySelectorAll('section[data-section-id]').forEach((item) => {
                    item.classList.remove('border-indigo-500', 'ring-2', 'ring-indigo-100');
                });
            });

            section.addEventListener('dragover', (event) => {
                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
                section.classList.add('border-indigo-500', 'ring-2', 'ring-indigo-100');
            });

            section.addEventListener('dragleave', () => {
                section.classList.remove('border-indigo-500', 'ring-2', 'ring-indigo-100');
            });

            section.addEventListener('drop', async (event) => {
                event.preventDefault();
                section.classList.remove('border-indigo-500', 'ring-2', 'ring-indigo-100');

                const draggedKey = event.dataTransfer.getData('text/plain');
                const targetKey = section.getAttribute('wire:key');
                if (!draggedKey || !targetKey || draggedKey === targetKey) return;

                const container = section.parentElement;
                const dragged = [...container.querySelectorAll('section[data-section-id][wire\\:key]')]
                    .find((item) => item.getAttribute('wire:key') === draggedKey);
                if (!dragged) return;

                const rect = section.getBoundingClientRect();
                const insertBefore = event.clientY < rect.top + rect.height / 2;
                if (insertBefore) {
                    container.insertBefore(dragged, section);
                } else {
                    container.insertBefore(dragged, section.nextSibling);
                }

                const orderedIds = [...container.querySelectorAll('section[data-section-id][wire\\:key]')]
                    .map((item) => item.getAttribute('wire:key').replace(/^builder-section-/, '').replace(/^section-/, ''));

                const component = getBuilderComponent();
                if (component) {
                    await component.reorderSections(orderedIds);
                }
            });
        });
    };

    const boot = () => {
        bootInlineEditing();
        bootVisualSelection();
        bootDragAndDrop();
    };

    window.finderBuilder = { boot };

    document.addEventListener('builder-dirty', scheduleSave);
    document.addEventListener('builder-sections-reordered', scheduleSave);
    document.addEventListener('builder-saved', () => {
        window.clearTimeout(saveTimer);
    });

    document.addEventListener('livewire:navigated', boot);
    document.addEventListener('DOMContentLoaded', boot);

    const observer = new MutationObserver(() => boot());
    observer.observe(document.body, { childList: true, subtree: true });
})();