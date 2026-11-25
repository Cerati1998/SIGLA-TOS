<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Keeps photos and their thumbnails on the private disk, where only the application serves them. It knows nothing of
 * what the photo belongs to, so any screen that takes photos can use it.
 */
class PhotoStorage
{
    private const string DISK = 'local';

    /**
     * Store a photo and its thumbnail in a directory, under the same random name.
     */
    public function store(UploadedFile $photo, UploadedFile $thumbnail, string $directory): StoredPhoto
    {
        $name = Str::ulid()->toBase32().'.jpg';

        $path = $photo->storeAs($directory, $name, self::DISK);
        $thumbnailPath = $thumbnail->storeAs($directory.'/thumbnails', $name, self::DISK);

        if ($path === false || $thumbnailPath === false) {
            throw new RuntimeException('No se pudo guardar la foto.');
        }

        return new StoredPhoto($path, $thumbnailPath);
    }

    /**
     * Delete a photo and its thumbnail.
     */
    public function delete(StoredPhoto $storedPhoto): void
    {
        Storage::disk(self::DISK)->delete([$storedPhoto->path, $storedPhoto->thumbnailPath]);
    }

    /**
     * Get the response that shows a stored file in the browser.
     */
    public function response(string $path): StreamedResponse
    {
        return Storage::disk(self::DISK)->response($path, headers: ['Cache-Control' => 'private, max-age=604800']);
    }

    /**
     * Get a stored JPEG as a data URI, to put it inside a PDF. A missing file gives an empty string.
     */
    public function dataUri(string $path): string
    {
        $contents = Storage::disk(self::DISK)->get($path);

        return $contents === null ? '' : 'data:image/jpeg;base64,'.base64_encode($contents);
    }

    /**
     * Get a stored JPEG as a data URI shrunk to at most `maxSide` px on its longest side, so a PDF shows it sharp
     * without carrying the whole photo (T85). Without GD, or when it is already small, it goes as it is.
     */
    public function resizedDataUri(string $path, int $maxSide): string
    {
        $contents = Storage::disk(self::DISK)->get($path);

        if ($contents === null) {
            return '';
        }

        $image = function_exists('imagecreatefromstring') ? @imagecreatefromstring($contents) : false;

        if ($image === false) {
            return 'data:image/jpeg;base64,'.base64_encode($contents);
        }

        $longestSide = max(imagesx($image), imagesy($image));

        if ($longestSide > $maxSide) {
            $scaled = imagescale($image, (int) round(imagesx($image) * $maxSide / $longestSide), (int) round(imagesy($image) * $maxSide / $longestSide));
            $contents = $scaled === false ? $contents : $this->jpeg($scaled);
        }

        return 'data:image/jpeg;base64,'.base64_encode($contents);
    }

    /**
     * Encode an image as a JPEG.
     */
    private function jpeg(\GdImage $image): string
    {
        ob_start();
        imagejpeg($image, null, 80);

        return (string) ob_get_clean();
    }
}
