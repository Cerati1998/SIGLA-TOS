<?php

namespace App\Actions\Certifications;

use App\Enums\CertificationSectionType;
use App\Models\Certification;
use App\Models\CertificationPhoto;
use App\Models\CertificationStep;
use App\Models\SecurityDeviceReplacement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * What the detail and the PDF of a certification show (H20, criteria 4 to 6): each inspection with its inspectors and
 * its start and end, taken from its first and its last photo (H18, criterion 8), and each section with its devices
 * and its photos.
 *
 * @phpstan-type Inspection array{type: CertificationSectionType, sections: int, photos: int, inspectors: list<string>, startedAt: Carbon|null, finishedAt: Carbon|null}
 * @phpstan-type Description array{inspections: list<Inspection>, steps: Collection<int, CertificationStep>, photos: int, devices: int, replacements: Collection<int, SecurityDeviceReplacement>}
 */
class DescribeCertification
{
    /**
     * Describe the certification, loading everything it shows at once.
     *
     * @return Description
     */
    public function handle(Certification $certification): array
    {
        $certification->loadMissing([
            'shippingLine', 'carrier', 'driver', 'tractor', 'semiTrailer', 'kit.kitType', 'yardZone', 'certifier', 'closer', 'lastReopening.reopener',
        ]);
        $steps = $certification->steps()
            ->with(['section', 'devices.lot.securityDeviceType', 'photos.uploader'])
            ->get();

        return [
            'inspections' => array_map(
                fn (CertificationSectionType $type): array => $this->inspection($type, $steps->where('type', $type)),
                CertificationSectionType::inspectionTypes(),
            ),
            'steps' => $steps,
            'photos' => $steps->sum(fn (CertificationStep $step): int => $step->photos->count()),
            'devices' => $steps->sum(fn (CertificationStep $step): int => $step->devices->count()),
            'replacements' => $certification->replacements()
                ->with(['removedDevice.lot.securityDeviceType', 'installedDevice', 'bulkDispatch', 'step.section', 'replacer'])
                ->get(),
        ];
    }

    /**
     * Describe an inspection from the photos of its sections.
     *
     * @param  Collection<int, CertificationStep>  $steps
     * @return Inspection
     */
    private function inspection(CertificationSectionType $type, Collection $steps): array
    {
        /** @var Collection<int, CertificationPhoto> $photos */
        $photos = $steps->flatMap(fn (CertificationStep $step) => $step->photos)->sortBy('created_at')->values();

        return [
            'type' => $type,
            'sections' => $steps->count(),
            'photos' => $photos->count(),
            'inspectors' => array_values(array_unique($photos->map(fn (CertificationPhoto $photo): string => $photo->uploader->name)->all())),
            'startedAt' => $photos->first()?->created_at,
            'finishedAt' => $photos->last()?->created_at,
        ];
    }
}
