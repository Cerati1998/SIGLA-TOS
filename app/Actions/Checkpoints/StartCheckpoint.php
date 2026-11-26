<?php

namespace App\Actions\Checkpoints;

use App\Enums\CertificationSectionType;
use App\Enums\CheckpointSectionKind;
use App\Models\Certification;
use App\Models\CertificationSection;
use App\Models\Checkpoint;
use App\Models\SecurityDevice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Give a certification its checkpoint the first time someone opens the unit (H34, decision of 2026-11-24): the active
 * checkpoint sections of the catalog are copied as its steps, in their order, and each verification step gets one
 * unmarked mark for each series the certification left in the sections it verifies (criterion 6), so later changes
 * in the catalog do not change it. If the checkpoint already exists, it is the one returned.
 */
class StartCheckpoint
{
    public function handle(Certification $certification): Checkpoint
    {
        return DB::transaction(function () use ($certification): Checkpoint {
            $certification = Certification::query()->lockForUpdate()->findOrFail($certification->id);

            if ($certification->checkpoint()->exists()) {
                return $certification->checkpoint()->firstOrFail();
            }

            if (! $certification->isClosed() || $certification->isAnnulled()) {
                throw ValidationException::withMessages([
                    'certification' => 'Solo se hace el checkpoint de una unidad con la certificación cerrada.',
                ]);
            }

            $checkpoint = $certification->checkpoint()->create();

            self::sections()->each(function (CertificationSection $section, int $index) use ($checkpoint, $certification): void {
                $kind = $section->checkpoint_kind ?? CheckpointSectionKind::Photos;

                $step = $checkpoint->steps()->create([
                    'certification_section_id' => $section->id,
                    'kind' => $kind,
                    'position' => $index + 1,
                ]);

                if ($kind === CheckpointSectionKind::Verification) {
                    $this->seriesToVerify($certification, $section)
                        ->each(fn (SecurityDevice $device) => $step->marks()->create(['security_device_id' => $device->id]));
                }
            });

            return $checkpoint;
        });
    }

    /**
     * Get the active checkpoint sections, in the single order of the catalog (H33, criterion 6), with the sections
     * they verify.
     *
     * @return Collection<int, CertificationSection>
     */
    public static function sections(): Collection
    {
        return CertificationSection::query()
            ->where('type', CertificationSectionType::Checkpoint)
            ->whereNotNull('sort_order')
            ->orderBy('sort_order')
            ->with('verifiedSections:id')
            ->get()
            ->values()
            ->toBase();
    }

    /**
     * Get the devices the certification left in the sections a verification section verifies, by serial number. A
     * linked section deactivated afterwards still shows its series (H33, criterion 8).
     *
     * @return Collection<int, SecurityDevice>
     */
    private function seriesToVerify(Certification $certification, CertificationSection $section): Collection
    {
        return $certification->steps()
            ->whereIn('certification_section_id', $section->verifiedSections->modelKeys())
            ->with('devices')
            ->get()
            ->flatMap->devices
            ->unique('id')
            ->sortBy('serial_number')
            ->values()
            ->toBase();
    }
}
