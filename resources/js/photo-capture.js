/**
 * The preparation of a photo taken with the camera of the phone before it is uploaded (H18), with what the browser
 * already has and no package: the photo is turned upright, shrunk to at most 1920 px on its longest side and saved as
 * a JPEG of about 700 KB, with the watermark printed on it. A thumbnail of 320 px is made from it too, so the photos
 * of a screen load light. Any screen that takes photos can use it through `window.preparePhoto`.
 */
const DEFAULTS = {
    maxSide: 1920,
    thumbnailSide: 320,
    quality: 0.82,
    minQuality: 0.5,
    targetBytes: 700 * 1024,
    watermark: '',
};

/**
 * Draw an image on a canvas whose longest side is at most `maxSide`.
 */
function drawScaled(source, maxSide) {
    const scale = Math.min(1, maxSide / Math.max(source.width, source.height));
    const canvas = document.createElement('canvas');

    canvas.width = Math.round(source.width * scale);
    canvas.height = Math.round(source.height * scale);
    canvas.getContext('2d').drawImage(source, 0, 0, canvas.width, canvas.height);

    return canvas;
}

/**
 * Print the watermark in white over a dark band at the bottom of the photo, so it reads on any background.
 */
function printWatermark(canvas, text) {
    if (text === '') {
        return;
    }

    const context = canvas.getContext('2d');
    const fontSize = Math.max(16, Math.round(canvas.width / 45));
    const padding = Math.round(fontSize * 0.6);
    const bandHeight = fontSize + padding * 2;

    context.fillStyle = 'rgba(0, 0, 0, 0.55)';
    context.fillRect(0, canvas.height - bandHeight, canvas.width, bandHeight);
    context.fillStyle = '#ffffff';
    context.font = `600 ${fontSize}px sans-serif`;
    context.textBaseline = 'middle';
    context.fillText(text, padding, canvas.height - bandHeight / 2);
}

function toJpeg(canvas, quality) {
    return new Promise((resolve, reject) => {
        canvas.toBlob((blob) => (blob ? resolve(blob) : reject(new Error('No se pudo preparar la foto.'))), 'image/jpeg', quality);
    });
}

/**
 * Prepare a photo and its thumbnail. When the photo is still heavier than the target, it is saved again with a lower
 * quality, down to a floor that keeps it readable.
 *
 * @returns {Promise<{photo: Blob, thumbnail: Blob}>}
 */
async function preparePhoto(file, options = {}) {
    const settings = { ...DEFAULTS, ...options };
    const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
    const canvas = drawScaled(bitmap, settings.maxSide);

    bitmap.close();
    printWatermark(canvas, settings.watermark);

    let quality = settings.quality;
    let photo = await toJpeg(canvas, quality);

    while (photo.size > settings.targetBytes && quality > settings.minQuality) {
        quality = Math.max(settings.minQuality, quality - 0.1);
        photo = await toJpeg(canvas, quality);
    }

    const thumbnail = await toJpeg(drawScaled(canvas, settings.thumbnailSide), 0.75);

    return { photo, thumbnail };
}

window.preparePhoto = preparePhoto;
