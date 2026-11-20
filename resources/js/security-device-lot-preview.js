/**
 * Format a whole number with a space between thousands, as in 10 000.
 */
const formatNumber = (number) => String(number).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

const isWholeNumber = (value) => /^\d+$/.test(String(value ?? '').trim());

/**
 * The preview of a lot of security devices (H07): how many devices the range brings and its first and last serial numbers, built as prefix + zero-padded correlative + optional suffix.
 * A type that does not use a series has neither prefix nor suffix, and one that does not require approval has its devices born Available (T62): the form hides the series and warns about it.
 * It reads the fields of the Livewire form in the browser, without asking the server. The server checks the same rules on save.
 */
document.addEventListener('alpine:init', () => {
    window.Alpine.data('securityDeviceLotPreview', (maxDevices, typeBoxes) => ({
        get selectedTypeBoxes() {
            return typeBoxes[this.$wire.form.securityDeviceTypeId] ?? { usesSeries: true, requiresApproval: true };
        },

        get usesSeries() {
            return this.selectedTypeBoxes.usesSeries;
        },

        get requiresApproval() {
            return this.selectedTypeBoxes.requiresApproval;
        },

        get correlativeColumns() {
            return this.usesSeries ? 'md:col-span-3' : 'md:col-span-2';
        },

        get preview() {
            const form = this.$wire.form;
            const prefix = this.usesSeries ? String(form.seriesPrefix ?? '').trim().toUpperCase() : '';
            const suffix = this.usesSeries ? String(form.seriesSuffix ?? '').trim().toUpperCase() : '';
            const empty = { quantity: null, first: null, last: null, error: null };

            if ((this.usesSeries && prefix === '') || ![form.minDigits, form.seriesStart, form.seriesEnd].every(isWholeNumber)) {
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

            const serialNumber = (correlative) => prefix + String(correlative).padStart(Number(form.minDigits), '0') + suffix;

            return { quantity: formatNumber(quantity), first: serialNumber(start), last: serialNumber(end), error: null };
        },
    }));
});
