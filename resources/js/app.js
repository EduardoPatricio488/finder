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

    const bootDragAndDrop = () => {
        const sections = [...document.querySelectorAll('.builder-section[wire\\:key]')];
        if (!sections.length) return;

        sections.forEach((section) => {
            if (section.dataset.finderDragReady === '1') return;

            section.dataset.finderDragReady = '1';
            section.draggable = true;

            section.addEventListener('dragstart', (event) => {
                section.classList.add('opacity-60');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', section.getAttribute('wire:key') || '');
            });

            section.addEventListener('dragend', () => {
                section.classList.remove('opacity-60');
                document.querySelectorAll('.builder-section').forEach((item) => {
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
                const dragged = [...container.querySelectorAll('.builder-section[wire\\:key]')]
                    .find((item) => item.getAttribute('wire:key') === draggedKey);
                if (!dragged) return;

                const rect = section.getBoundingClientRect();
                const insertBefore = event.clientY < rect.top + rect.height / 2;
                if (insertBefore) {
                    container.insertBefore(dragged, section);
                } else {
                    container.insertBefore(dragged, section.nextSibling);
                }

                const orderedIds = [...container.querySelectorAll('.builder-section[wire\\:key]')]
                    .map((item) => item.getAttribute('wire:key').replace(/^section-/, ''));

                const component = getBuilderComponent();
                if (component) {
                    await component.reorderSections(orderedIds);
                }
            });
        });
    };

    const boot = () => {
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

    const observer = new MutationObserver(() => bootDragAndDrop());
    observer.observe(document.body, { childList: true, subtree: true });
})();