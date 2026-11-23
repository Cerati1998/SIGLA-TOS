/**
 * The `x-digits-only` directive: a field, or a wrapper of fields, that only takes digits. Any other character typed,
 * pasted or dropped is stopped before it reaches the field, so the field never shows it. The server checks the value
 * again when it is saved.
 */
const NOT_A_DIGIT = /\D/;

document.addEventListener('alpine:init', () => {
    window.Alpine.directive('digits-only', (el, directive, { cleanup }) => {
        const blockOtherCharacters = (event) => {
            const text = event.data ?? event.dataTransfer?.getData('text') ?? '';

            if (NOT_A_DIGIT.test(text)) {
                event.preventDefault();
            }
        };

        el.addEventListener('beforeinput', blockOtherCharacters);

        cleanup(() => el.removeEventListener('beforeinput', blockOtherCharacters));
    });
});
