/**
 * Format a whole number with a space between thousands, as in 10 000.
 */
const formatNumber = (number) => String(number).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

const isWholeNumber = (value) => /^\d+$/.test(String(value ?? '').trim());

/**
 * The preview of a lot of security devices (H07): how many devices the range brings and its first and last serial numbers, built as prefix + zero-padded correlative + optional suffix.
 * A type that does not use a series has neither prefix nor suffix, and one that does not require approval has its devices born Available (T62): the form hides the series and warns about it.
 * The quantity is not saved: typing it fills the final correlative from the initial one, and typing the final one counts it again (T75).
 * It reads the fields of the Livewire form in the browser, without asking the server. The server checks the same rules on save.
 */
document.addEventListener('alpine:init', () => {
    window.Alpine.data('securityDeviceLotPreview', (maxDevices, typeBoxes) => ({
        quantity: '',

        init() {
            this.countQuantity(this.$wire.form.seriesStart, this.$wire.form.seriesEnd);
        },

        get selectedTypeBoxes() {
            return typeBoxes[this.$wire.form.securityDeviceTypeId] ?? { usesSeries: true, requiresApproval: true };
        },

        get usesSeries() {
            return this.selectedTypeBoxes.usesSeries;
        },

        get requiresApproval() {
            return this.selectedTypeBoxes.requiresApproval;
        },

        /**
         * Count the devices of the range, or leave the quantity empty while the range is not complete or is backwards.
         */
        countQuantity(start, end) {
            const quantity = Number(end) - Number(start) + 1;

            this.quantity = isWholeNumber(start) && isWholeNumber(end) && quantity >= 1 ? String(quantity) : '';
        },

        /**
         * Fill the final correlative from the initial one and the quantity, when both are written.
         */
        fillEnd(start) {
            if (isWholeNumber(start) && isWholeNumber(this.quantity) && Number(this.quantity) >= 1) {
                this.$wire.form.seriesEnd = String(Number(start) + Number(this.quantity) - 1);
            }
        },

        quantityChanged(event) {
            this.quantity = event.target.value.replace(/\D/g, '');
            this.fillEnd(this.$wire.form.seriesStart);
        },

        startChanged(event) {
            if (isWholeNumber(this.quantity)) {
                this.fillEnd(event.target.value);
            } else {
                this.countQuantity(event.target.value, this.$wire.form.seriesEnd);
            }
        },

        endChanged(event) {
            this.countQuantity(this.$wire.form.seriesStart, event.target.value);
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
