/**
 * Format a whole number with a space between thousands, as in 10 000.
 */
const formatNumber = (number) => String(number).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

const isWholeNumber = (value) => /^\d+$/.test(String(value ?? '').trim());

/**
 * The preview of a lot of security devices (H07): how many devices the range brings and its first and last serial numbers.
 * It reads the fields of the Livewire form in the browser, without asking the server. The server checks the same rules on save.
 */
document.addEventListener('alpine:init', () => {
    window.Alpine.data('securityDeviceLotPreview', (maxDevices) => ({
        get preview() {
            const form = this.$wire.form;
            const prefix = String(form.seriesPrefix ?? '').trim().toUpperCase();
            const empty = { quantity: null, first: null, last: null, error: null };

            if (prefix === '' || ![form.minDigits, form.seriesStart, form.seriesEnd].every(isWholeNumber)) {
                return empty;
            }

            const start = Number(form.seriesStart);
            const end = Number(form.seriesEnd);
            const quantity = end - start + 1;

            if (quantity < 1) {
                return { ...empty, error: 'El correlativo inicial no puede ser mayor que el final.' };
            }

            if (quantity > maxDevices) {
                return {
                    ...empty,
                    error: `Un lote puede tener como máximo ${formatNumber(maxDevices)} dispositivos. Este rango tiene ${formatNumber(quantity)}.`,
                };
            }

            const serialNumber = (correlative) => prefix + String(correlative).padStart(Number(form.minDigits), '0');

            return { quantity: formatNumber(quantity), first: serialNumber(start), last: serialNumber(end), error: null };
        },
    }));
});
