const form = document.querySelector('[data-join-form]');
const button = document.querySelector('[data-join-button]');

form?.addEventListener('submit', () => {
    if (!(button instanceof HTMLButtonElement)) {
        return;
    }

    button.disabled = true;
    button.querySelector('span')?.replaceChildren('Creating your meeting…');
});
