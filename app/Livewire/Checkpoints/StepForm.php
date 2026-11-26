<?php

namespace App\Livewire\Checkpoints;

use App\Actions\Checkpoints\AddCheckpointPhoto;
use App\Actions\Checkpoints\CloseCheckpoint;
use App\Actions\Checkpoints\DeleteCheckpointPhoto;
use App\Actions\Checkpoints\MarkCheckpointSeries;
use App\Actions\Checkpoints\ReplaceCheckpointPhoto;
use App\Enums\CheckpointSectionKind;
use App\Models\CertificationStep;
use App\Models\Checkpoint;
use App\Models\CheckpointMark;
use App\Models\CheckpointPhoto;
use App\Models\CheckpointStep;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * The steps of the checkpoint of a unit (H34), one at a time, then the last step with the observations, the extra
 * photos, the summary and «Finalizar». The photos work as in the inspection (H18); a mark is saved when it is touched.
 * Moving between the steps happens in the browser. A closed checkpoint is only read (criterion 11).
 *
 * @property-read Collection<int, CheckpointStep> $steps
 * @property-read Collection<int, CheckpointPhoto> $extraPhotos
 * @property-read array<int, string> $deviceSections
 */
class StepForm extends Component
{
    use WithFileUploads;

    #[Locked]
    public Checkpoint $checkpoint;

    /**
     * The photo uploaded last, compressed in the browser, waiting to be saved in a step or as an extra one.
     */
    public ?TemporaryUploadedFile $photo = null;

    /**
     * The thumbnail of the uploaded photo, made in the browser.
     */
    public ?TemporaryUploadedFile $thumbnail = null;

    /**
     * The observations of the last step, saved when the field is left (criterion 7).
     */
    public string $observations = '';

    public function mount(): void
    {
        $this->observations = $this->checkpoint->observations ?? '';
    }

    /**
     * Get the steps of the checkpoint with their photos and marks, in their order.
     *
     * @return Collection<int, CheckpointStep>
     */
    #[Computed]
    public function steps(): Collection
    {
        return $this->checkpoint->steps()
            ->with(['section.verifiedSections', 'photos.uploader', 'marks.device', 'marks.marker'])
            ->get();
    }

    /**
     * Get the extra photos of the last step.
     *
     * @return Collection<int, CheckpointPhoto>
     */
    #[Computed]
    public function extraPhotos(): Collection
    {
        return $this->checkpoint->extraPhotos()->with('uploader')->get();
    }

    /**
     * Get, for each device of the certification, the section it was left in, with its type, to show it beside its series.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function deviceSections(): array
    {
        $sections = [];

        $this->checkpoint->certification->steps()->with(['section', 'devices:id'])->get()
            ->each(function (CertificationStep $step) use (&$sections): void {
                foreach ($step->devices as $device) {
                    $sections[$device->id] = $step->section->name.' ('.mb_strtolower($step->type->label()).')';
                }
            });

        return $sections;
    }

    /**
     * Save the uploaded photo in a photo step, or as an extra one without a step, as a new one or in the place of one of
     * its photos (criteria 5 and 7). It answers true to the browser, which then takes the photo out of its queue.
     */
    public function savePhoto(?int $stepId, ?int $replacedPhotoId, AddCheckpointPhoto $addPhoto, ReplaceCheckpointPhoto $replacePhoto): bool
    {
        if ($this->refusedBecauseClosed()) {
            return false;
        }

        $this->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg', 'max:3072'],
            'thumbnail' => ['required', 'image', 'mimes:jpg,jpeg', 'max:512'],
        ], [
            'photo.*' => 'La foto no es válida. Tómala otra vez.',
            'thumbnail.*' => 'La foto no es válida. Tómala otra vez.',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $step = $stepId === null ? null : $this->photoStep($stepId);
        /** @var TemporaryUploadedFile $photo */
        $photo = $this->photo;
        /** @var TemporaryUploadedFile $thumbnail */
        $thumbnail = $this->thumbnail;

        if ($replacedPhotoId === null) {
            $addPhoto->handle($this->checkpoint, $step, $photo, $thumbnail, $user);
        } else {
            $replacePhoto->handle($this->photoOf($step, $replacedPhotoId), $photo, $thumbnail, $user);
        }

        $this->reset('photo', 'thumbnail');
        unset($this->steps, $this->extraPhotos);

        return true;
    }

    /**
     * Delete a photo of a step or an extra one, once the inspector confirmed it.
     */
    public function deletePhoto(?int $stepId, int $photoId, DeleteCheckpointPhoto $deletePhoto): void
    {
        if ($this->refusedBecauseClosed()) {
            return;
        }

        $deletePhoto->handle($this->photoOf($stepId === null ? null : $this->photoStep($stepId), $photoId));

        unset($this->steps, $this->extraPhotos);

        $this->dispatch('toast', icon: 'success', title: 'Foto borrada');
    }

    /**
     * Mark a series of a verification step as correct or incorrect (criterion 6).
     */
    public function mark(int $markId, bool $isCorrect, MarkCheckpointSeries $markSeries): void
    {
        if ($this->refusedBecauseClosed()) {
            return;
        }

        /** @var User $user */
        $user = Auth::user();
        $mark = CheckpointMark::query()
            ->whereHas('step', fn (Builder $steps) => $steps->where('checkpoint_id', $this->checkpoint->id)->where('kind', CheckpointSectionKind::Verification))
            ->findOrFail($markId);

        $markSeries->handle($mark, $isCorrect, $user);

        unset($this->steps);
    }

    /**
     * Save the observations when the inspector leaves the field, so a checkpoint left half done keeps them.
     */
    public function updatedObservations(): void
    {
        if ($this->refusedBecauseClosed()) {
            return;
        }

        $this->validate(['observations' => ['nullable', 'string', 'max:2000']], [
            'observations.max' => 'Las observaciones pueden tener hasta 2000 caracteres.',
        ]);

        $this->checkpoint->update(['observations' => CloseCheckpoint::clean($this->observations)]);
    }

    /**
     * Close the checkpoint when every step is complete and every series is correct (criteria 8 and 9).
     */
    public function finish(CloseCheckpoint $closeCheckpoint): void
    {
        if ($this->refusedBecauseClosed()) {
            return;
        }

        $this->validate(['observations' => ['nullable', 'string', 'max:2000']], [
            'observations.max' => 'Las observaciones pueden tener hasta 2000 caracteres.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $closeCheckpoint->handle($this->checkpoint, $this->observations, $user);

        $this->dispatch('toast', icon: 'success', title: 'Checkpoint cerrado', text: $this->checkpoint->certification->container.' quedó con su checkpoint cerrado.');

        $this->redirectRoute('intranet.checkpoints.pending', navigate: true);
    }

    /**
     * Refuse a change when the checkpoint was closed, even if the screen was opened before (criterion 11).
     */
    private function refusedBecauseClosed(): bool
    {
        if (! $this->checkpoint->refresh()->isClosed()) {
            return false;
        }

        $this->dispatch('toast', icon: 'warning', title: 'Checkpoint cerrado', text: 'No se puede editar: el checkpoint ya está cerrado.');

        return true;
    }

    /**
     * Get a photo step of this checkpoint.
     */
    private function photoStep(int $stepId): CheckpointStep
    {
        return $this->checkpoint->steps()->where('kind', CheckpointSectionKind::Photos)->findOrFail($stepId);
    }

    /**
     * Get a photo of a step of this checkpoint, or an extra one when there is no step.
     */
    private function photoOf(?CheckpointStep $step, int $photoId): CheckpointPhoto
    {
        $photos = $this->checkpoint->photos();

        return ($step === null ? $photos->whereNull('checkpoint_step_id') : $photos->where('checkpoint_step_id', $step->id))->findOrFail($photoId);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('pages.checkpoints.step-form');
    }
}
