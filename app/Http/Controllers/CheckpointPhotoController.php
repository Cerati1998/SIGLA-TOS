<?php

namespace App\Http\Controllers;

use App\Models\Checkpoint;
use App\Models\CheckpointPhoto;
use App\Services\PhotoStorage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CheckpointPhotoController extends Controller
{
    /**
     * Show a photo of a checkpoint at its full size.
     */
    public function show(CheckpointPhoto $checkpointPhoto, PhotoStorage $photoStorage): StreamedResponse
    {
        return $photoStorage->response($checkpointPhoto->path);
    }

    /**
     * Show the thumbnail of a photo of a checkpoint.
     */
    public function thumbnail(CheckpointPhoto $checkpointPhoto, PhotoStorage $photoStorage): StreamedResponse
    {
        return $photoStorage->response($checkpointPhoto->thumbnail_path);
    }

    /**
     * Show the photo of the container of a checkpoint at its full size.
     */
    public function container(Checkpoint $checkpoint, PhotoStorage $photoStorage): StreamedResponse
    {
        abort_if($checkpoint->container_photo_path === null, 404);

        return $photoStorage->response($checkpoint->container_photo_path);
    }

    /**
     * Show the thumbnail of the photo of the container of a checkpoint.
     */
    public function containerThumbnail(Checkpoint $checkpoint, PhotoStorage $photoStorage): StreamedResponse
    {
        abort_if($checkpoint->container_thumbnail_path === null, 404);

        return $photoStorage->response($checkpoint->container_thumbnail_path);
    }
}
