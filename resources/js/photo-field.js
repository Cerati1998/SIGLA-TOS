/**
 * A single required photo of a form inside a modal (T77): taken with the camera of the phone, compressed in the
 * browser with `window.preparePhoto` and uploaded at once to two properties of the Livewire form, the photo and its
 * thumbnail. It shows the photo taken, opens it in a viewer, takes it again or removes it. The server checks the photo
 * again when the form is saved. A form that edits a record shows the photo it already has with `showSaved`; removing
 * that photo dispatches `photo-removed`, so the form can forget it when it is saved (H40). `pick` chooses the photo
 * from the phone instead of the camera, when the field offers it. The watermark starts with a label, or with what a function gives when the label changes
 * with the record of the modal.
 */
document.addEventListener('alpine:init', () => {
    window.Alpine.data('photoField', (photoProperty, thumbnailProperty, watermarkLabel) => ({
        thumbnailUrl: null,
        photoUrl: null,
        uploading: false,
        failed: false,
        viewerOpen: false,
        saved: false,

        init() {
            this.$watch(() => this.$wire.get(photoProperty), (value) => {
                if (! value && ! this.uploading) {
                    this.forget();
                }
            });
        },

        get hasPhoto() {
            return this.thumbnailUrl !== null && ! this.uploading;
        },

        take() {
            this.$refs.camera.click();
        },

        pick() {
            this.$refs.gallery.click();
        },

        showSaved(photoUrl, thumbnailUrl) {
            this.forget();
            this.photoUrl = photoUrl;
            this.thumbnailUrl = thumbnailUrl;
            this.saved = true;
        },

        openViewer() {
            this.viewerOpen = true;
        },

        closeViewer() {
            this.viewerOpen = false;
        },

        watermark() {
            const now = new Date();
            const pad = (value) => String(value).padStart(2, '0');
            const label = typeof watermarkLabel === 'function' ? watermarkLabel() : watermarkLabel;

            return `${label} · ${pad(now.getDate())}/${pad(now.getMonth() + 1)}/${now.getFullYear()} ${pad(now.getHours())}:${pad(now.getMinutes())}`;
        },

        async captured(event) {
            const file = event.target.files[0];
            event.target.value = '';

            if (! file) {
                return;
            }

            this.viewerOpen = false;
            this.failed = false;
            this.uploading = true;

            try {
                const { photo, thumbnail } = await window.preparePhoto(file, { watermark: this.watermark() });

                await this.send(photoProperty, photo);
                await this.send(thumbnailProperty, thumbnail);

                this.release();
                this.saved = false;
                this.photoUrl = URL.createObjectURL(photo);
                this.thumbnailUrl = URL.createObjectURL(thumbnail);
            } catch {
                this.failed = true;
            } finally {
                this.uploading = false;
            }
        },

        send(property, blob) {
            const file = new File([blob], 'foto.jpg', { type: 'image/jpeg' });

            return new Promise((resolve, reject) => this.$wire.upload(property, file, resolve, reject));
        },

        remove() {
            if (this.saved) {
                this.$dispatch('photo-removed');
            }

            this.viewerOpen = false;
            this.$wire.set(photoProperty, null, false);
            this.$wire.set(thumbnailProperty, null, false);
            this.forget();
        },

        forget() {
            this.release();
            this.photoUrl = null;
            this.thumbnailUrl = null;
            this.failed = false;
            this.saved = false;
        },

        release() {
            [this.photoUrl, this.thumbnailUrl].filter((url) => url?.startsWith('blob:')).forEach((url) => URL.revokeObjectURL(url));
        },
    }));
});
