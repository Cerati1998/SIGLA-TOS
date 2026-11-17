/**
 * The scanning of the pouches in the window «Despacho masivo de kits» (H15). Each Enter sends the pouch code to the
 * server by itself, without a save button, and the kit gets marked in the table. The list of the last scans shows what
 * was marked and what was not, with the reason. Only a scan that could not reach the server (no connection or a
 * server error) offers "Reintentar".
 */
const MAX_SCANS = 6;

document.addEventListener('alpine:init', () => {
    window.Alpine.data('kitDispatchScanner', () => ({
        scans: [],
        nextId: 1,

        submit(input) {
            const pouchCode = String(input.value ?? '').trim().toUpperCase();

            input.value = '';

            if (pouchCode === '') {
                return;
            }

            const scan = { id: this.nextId++, serialNumber: pouchCode, state: 'pending', message: 'Buscando…' };

            this.scans = [scan, ...this.scans].slice(0, MAX_SCANS);
            this.send(scan.id, input);
        },

        retry(scan) {
            this.send(scan.id, null);
        },

        async send(scanId, input) {
            const scan = this.scans.find((item) => item.id === scanId);

            if (!scan) {
                return;
            }

            scan.state = 'pending';
            scan.message = 'Buscando…';

            let result;

            try {
                result = await this.$wire.mark(scan.serialNumber);
            } catch {
                result = undefined;
            }

            if (!result || typeof result.ok !== 'boolean') {
                scan.state = 'failed';
                scan.message = 'Sin conexión: no se pudo marcar.';
            } else {
                scan.state = result.ok ? 'added' : 'rejected';
                scan.message = result.message;
            }

            this.$nextTick(() => input?.focus());
        },
    }));
});
