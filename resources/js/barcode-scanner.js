/**
 * The reading of a barcode with the camera of the phone (H17), for the `x-camera-scanner` component. It uses the
 * barcode reader of the browser when it has one (Chrome on Android) and the barcode-detector package when it does
 * not (Safari on iPhone). The .wasm file of that package is served by SIGLA itself, never by an outside CDN.
 * When a code is read, the camera turns off and the component gets a `barcode-detected` event with its value.
 */
import wasmUrl from 'zxing-wasm/reader/zxing_reader.wasm?url';

const FORMATS = ['code_128', 'code_39', 'code_93', 'codabar', 'ean_13', 'ean_8', 'itf', 'upc_a', 'upc_e', 'qr_code', 'data_matrix'];
const SCAN_EVERY_MS = 200;

async function createDetector() {
    if ('BarcodeDetector' in window) {
        const supported = await window.BarcodeDetector.getSupportedFormats();
        const formats = FORMATS.filter((format) => supported.includes(format));

        if (formats.length > 0) {
            return new window.BarcodeDetector({ formats });
        }
    }

    const { BarcodeDetector, prepareZXingModule } = await import('barcode-detector/ponyfill');

    prepareZXingModule({
        overrides: {
            locateFile: (path, prefix) => (path.endsWith('.wasm') ? wasmUrl : prefix + path),
        },
    });

    return new BarcodeDetector({ formats: FORMATS });
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('barcodeScanner', () => ({
        open: false,
        error: null,
        stream: null,
        timer: null,

        async start() {
            this.open = true;
            this.error = null;

            if (!navigator.mediaDevices?.getUserMedia) {
                this.error = 'Este navegador no puede abrir la cámara. Escribe el código.';

                return;
            }

            try {
                const [detector, stream] = await Promise.all([
                    createDetector(),
                    navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false }),
                ]);

                if (!this.open) {
                    stream.getTracks().forEach((track) => track.stop());

                    return;
                }

                this.stream = stream;
                this.$refs.video.srcObject = stream;
                await this.$refs.video.play();
                this.scan(detector);
            } catch {
                this.stopCamera();
                this.error = 'No se pudo abrir la cámara. Dale permiso en el navegador o escribe el código.';
            }
        },

        scan(detector) {
            const tick = async () => {
                if (!this.open || !this.stream) {
                    return;
                }

                try {
                    const codes = await detector.detect(this.$refs.video);
                    const value = String(codes[0]?.rawValue ?? '').trim();

                    if (value !== '') {
                        this.close();
                        this.$root.dispatchEvent(new CustomEvent('barcode-detected', { detail: { value }, bubbles: true }));

                        return;
                    }
                } catch {
                    // A frame that cannot be read is skipped; the next one is tried.
                }

                this.timer = window.setTimeout(tick, SCAN_EVERY_MS);
            };

            tick();
        },

        close() {
            this.open = false;
            this.stopCamera();
        },

        stopCamera() {
            window.clearTimeout(this.timer);
            this.stream?.getTracks().forEach((track) => track.stop());
            this.stream = null;
        },

        destroy() {
            this.close();
        },
    }));
});
