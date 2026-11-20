<?php

namespace App\Actions\Certifications;

use App\Enums\CertificationSectionType;
use App\Models\Certification;
use App\Models\CertificationPhoto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * The assigned units whose internal or external inspection still has a section without a photo, for the card of that
 * inspection (H18, criterion 2), with how far it went and how the other inspection goes.
 */
class ListPendingInspections
{
    /**
     * Get the pending units of an inspection, the ones assigned last first.
     *
     * @return list<array{id: int, container: string, pouchCode: string, shippingLine: string, done: int, total: int, otherDone: int, otherTotal: int, lastPhoto: CertificationPhoto|null, startedAt: Carbon}>
     */
    public function handle(CertificationSectionType $type): array
    {
        $other = $type === CertificationSectionType::Internal ? CertificationSectionType::External : CertificationSectionType::Internal;

        /** @var Collection<int, Certification> $certifications */
        $certifications = $this->withProgress(Certification::query(), $type, $other)
            ->with(['kit:id,pouch_code', 'shippingLine:id,code'])
            ->latest('started_at')
            ->get()
            ->filter(fn (Certification $certification): bool => $this->count($certification, 'done') < $this->count($certification, 'total'));

        $lastPhotos = $this->lastPhotos($certifications->modelKeys(), $type);

        return array_values($certifications
            ->map(fn (Certification $certification): array => [
                'id' => $certification->id,
                'container' => $certification->container,
                'pouchCode' => $certification->kit->pouch_code,
                'shippingLine' => $certification->shippingLine->code,
                'done' => $this->count($certification, 'done'),
                'total' => $this->count($certification, 'total'),
                'otherDone' => $this->count($certification, 'other_done'),
                'otherTotal' => $this->count($certification, 'other_total'),
                'lastPhoto' => $lastPhotos[$certification->id] ?? null,
                'startedAt' => $certification->started_at,
            ])
            ->all());
    }

    /**
     * Get how many units have each inspection pending, for the cards of the panel.
     *
     * @return array{internal: int, external: int}
     */
    public function counts(): array
    {
        return [
            'internal' => count($this->handle(CertificationSectionType::Internal)),
            'external' => count($this->handle(CertificationSectionType::External)),
        ];
    }

    /**
     * Count the sections of each inspection and the ones with a photo.
     *
     * @param  Builder<Certification>  $query
     * @return Builder<Certification>
     */
    private function withProgress(Builder $query, CertificationSectionType $type, CertificationSectionType $other): Builder
    {
        return $query->withCount([
            'steps as total' => fn (Builder $steps) => $steps->where('type', $type),
            'steps as done' => fn (Builder $steps) => $steps->where('type', $type)->has('photos'),
            'steps as other_total' => fn (Builder $steps) => $steps->where('type', $other),
            'steps as other_done' => fn (Builder $steps) => $steps->where('type', $other)->has('photos'),
        ]);
    }

    /**
     * Get the last photo of the inspection of each certification, with who uploaded it.
     *
     * @param  array<int, int|string>  $certificationIds
     * @return array<int, CertificationPhoto>
     */
    private function lastPhotos(array $certificationIds, CertificationSectionType $type): array
    {
        return CertificationPhoto::query()
            ->with(['uploader:id,name', 'step:id,certification_id'])
            ->whereHas('step', fn (Builder $steps) => $steps->whereIn('certification_id', $certificationIds)->where('type', $type))
            ->latest()
            ->get()
            ->unique(fn (CertificationPhoto $photo): int => $photo->step->certification_id)
            ->mapWithKeys(fn (CertificationPhoto $photo): array => [$photo->step->certification_id => $photo])
            ->all();
    }

    private function count(Certification $certification, string $attribute): int
    {
        return (int) $certification->getAttribute($attribute);
    }
}
