/**
 * The queue of photos of a screen with steps, shared by the inspection (H18) and the checkpoint (H34). Each photo is
 * taken with the camera of the phone, compressed in the browser with `window.preparePhoto`, with the date, the time
 * and the container printed on it, and uploaded at once to the `photo` and `thumbnail` properties of the component,
 * which then saves it with `savePhoto(stepId, replacedPhotoId)` and answers true. A photo that fails stays in its
 * place to be retried. The screen spreads it into its own `Alpine.data()` and has an `x-ref="camera"` file input.
 */
window.photoUploads = (container) => ({
    container,
    target: null,
    pending: {},
    queue: [],
    uploading: false,

    pendingOf(stepId) {
        return this.pending[stepId ?? 'extra'] ?? [];
    },

    label(item) {
        return {
            preparing: 'Preparando…',
            uploading: `Subiendo ${item.progress} %`,
            failed: 'No se subió · Reintentar',
        }[item.status];
    },

    takePhoto(stepId, replacedPhotoId = null) {
        this.target = { stepId, replacedPhotoId };
        this.$refs.camera.click();
    },

    watermark() {
        const now = new Date();
        const pad = (value) => String(value).padStart(2, '0');

        return `${pad(now.getDate())}/${pad(now.getMonth() + 1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())} · ${this.container}`;
    },

    async captured(event) {
        const file = event.target.files[0];
        event.target.value = '';

        if (! file || this.target === null) {
            return;
        }

        const { stepId, replacedPhotoId } = this.target;
        const key = stepId ?? 'extra';
        this.target = null;
        this.pending[key] = [...this.pendingOf(stepId), {
            key: `${Date.now()}-${Math.random()}`, stepId, replacedPhotoId, status: 'preparing', progress: 0, url: URL.createObjectURL(file), blobs: null,
        }];
        const item = this.pending[key].at(-1);

        try {
            item.blobs = await window.preparePhoto(file, { watermark: this.watermark() });
            URL.revokeObjectURL(item.url);
            item.url = URL.createObjectURL(item.blobs.thumbnail);
            this.queue.push(item);
            this.upload();
        } catch {
            item.status = 'failed';
        }
    },

    retry(item) {
        if (item.status !== 'failed' || item.blobs === null) {
            return;
        }

        this.queue.push(item);
        this.upload();
    },

    send(property, blob, item) {
        const file = new File([blob], 'foto.jpg', { type: 'image/jpeg' });

        return new Promise((resolve, reject) => {
            this.$wire.upload(property, file, resolve, reject, (event) => {
                if (property === 'photo') {
                    item.progress = event.detail.progress;
                }
            });
        });
    },

    async upload() {
        if (this.uploading) {
            return;
        }

        this.uploading = true;

        while (this.queue.length > 0) {
            const item = this.queue.shift();
            const key = item.stepId ?? 'extra';
            item.status = 'uploading';
            item.progress = 0;

            try {
                await this.send('photo', item.blobs.photo, item);
                await this.send('thumbnail', item.blobs.thumbnail, item);

                if (await this.$wire.savePhoto(item.stepId, item.replacedPhotoId) !== true) {
                    throw new Error('not saved');
                }

                URL.revokeObjectURL(item.url);
                this.pending[key] = this.pendingOf(item.stepId).filter((pending) => pending.key !== item.key);
            } catch {
                item.status = 'failed';
            }
        }

        this.uploading = false;
    },
});
