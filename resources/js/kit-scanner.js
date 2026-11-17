/**
 * The scanning of the devices of a kit (H14). Each Enter in the field of a slot sends the serial number to the server
 * by itself, without a save button, and the list of the last scans shows what was added and what was not, with the
 * reason. Only a scan that could not reach the server (no connection or a server error) offers "Reintentar".
 * When a slot fills up, its field gets disabled and the cursor moves to the next slot that still needs devices.
 */
const MAX_SCANS = 8;

document.addEventListener('alpine:init', () => {
    window.Alpine.data('kitScanner', () => ({
        scans: [],
        nextId: 1,

        init() {
            this.$nextTick(() => this.focusSlot(null));
        },

        submit(slotKey, input) {
            const serialNumber = String(input.value ?? '').trim().toUpperCase();

            input.value = '';

            if (serialNumber === '') {
                return;
            }

            const scan = { id: this.nextId++, slotKey, serialNumber, state: 'pending', message: 'Guardando…' };

            this.scans = [scan, ...this.scans].slice(0, MAX_SCANS);
            this.send(scan.id);
        },

        retry(scan) {
            this.send(scan.id);
        },

        async send(scanId) {
            const scan = this.scans.find((item) => item.id === scanId);

            if (!scan) {
                return;
            }

            scan.state = 'pending';
            scan.message = 'Guardando…';

            let result;

            try {
                result = await this.$wire.add(scan.slotKey, scan.serialNumber);
            } catch {
                result = undefined;
            }

            if (!result || typeof result.ok !== 'boolean') {
                scan.state = 'failed';
                scan.message = 'Sin conexión: no se pudo guardar.';
            } else {
                scan.state = result.ok ? 'added' : 'rejected';
                scan.message = result.message;
            }

            this.$nextTick(() => this.focusSlot(scan.slotKey));
        },

        lastFor(slotKey) {
            return this.scans.find((scan) => scan.slotKey === slotKey) ?? null;
        },

        focusSlot(slotKey) {
            const inputs = [...this.$root.querySelectorAll('[data-slot-input]')];
            const current = inputs.findIndex((input) => input.dataset.slotInput === slotKey);

            if (current !== -1 && !inputs[current].disabled) {
                inputs[current].focus();

                return;
            }

            const ordered = current === -1 ? inputs : [...inputs.slice(current + 1), ...inputs.slice(0, current)];

            ordered.find((input) => !input.disabled)?.focus();
        },
    }));
});
