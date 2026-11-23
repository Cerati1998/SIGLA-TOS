<?php

namespace App\Services;

/**
 * Where a photo and its thumbnail were stored on the private disk.
 */
readonly class StoredPhoto
{
    public function __construct(
        public string $path,
        public string $thumbnailPath,
    ) {}
}
