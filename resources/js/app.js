document.addEventListener('submit', async (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const componentRoot = form.closest('[wire\\:id]');
    const componentId = componentRoot?.getAttribute('wire:id');

    if (!componentId || !window.Livewire) {
        return;
    }

    const component = window.Livewire.find(componentId);

    if (!component) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();

    const textarea = form.querySelector('textarea');
    const emailInput = form.querySelector('input[type="email"]');

    try {
        if (textarea) {
            const inputs = form.querySelectorAll('input');
            const nameInput = inputs[0];

            await component.set('contactName', nameInput?.value ?? '');
            await component.set('contactEmail', emailInput?.value ?? '');
            await component.set('contactMessage', textarea.value);
            await component.call('submitContact');
        } else if (emailInput) {
            await component.set('newsletterEmail', emailInput.value);
            await component.call('subscribeNewsletter');
        }
    } catch (error) {
        console.error('Finder public form submission failed.', error);
    }
}, true);
