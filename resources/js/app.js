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
});
