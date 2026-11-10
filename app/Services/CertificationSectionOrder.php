<?php

namespace App\Services;

use App\Models\CertificationSection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Keeps the order of the active sections, which is the order of the steps of the certification (H11).
 * The active sections always hold the positions 1, 2, 3… with no gaps; a deactivated one has no position.
 */
class CertificationSectionOrder
{
    /**
     * Get the active sections in their order.
     *
     * @return Collection<int, CertificationSection>
     */
    public function activeSections(): Collection
    {
        return CertificationSection::query()->orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * Get the position for a section that goes at the end.
     */
    public function nextPosition(): int
    {
        return (int) CertificationSection::query()->max('sort_order') + 1;
    }

    /**
     * Take the section out of the order and deactivate it. The sections after it move up one place.
     */
    public function deactivate(CertificationSection $section): void
    {
        DB::transaction(function () use ($section): void {
            $section->update(['sort_order' => null]);
            $section->delete();

            $this->renumber($this->activeSections());
        });
    }

    /**
     * Reactivate the section at the end of the order.
     */
    public function reactivate(CertificationSection $section): void
    {
        DB::transaction(function () use ($section): void {
            $section->restore();
            $section->update(['sort_order' => $this->nextPosition()]);
        });
    }

    /**
     * Move an active section to a position of the order, counted from zero.
     */
    public function moveTo(CertificationSection $section, int $position): void
    {
        DB::transaction(function () use ($section, $position): void {
            $sections = $this->activeSections()->reject(fn (CertificationSection $active): bool => $active->is($section))->values();
            $position = max(0, min($position, $sections->count()));

            $sections->splice($position, 0, [$section]);

            $this->renumber($sections);
        });
    }

    /**
     * Give the sections the positions 1, 2, 3… in the order received, saving only the ones that change.
     *
     * @param  Collection<int, CertificationSection>  $sections
     */
    private function renumber(Collection $sections): void
    {
        $sections->values()->each(function (CertificationSection $section, int $index): void {
            if ($section->sort_order !== $index + 1) {
                $section->update(['sort_order' => $index + 1]);
            }
        });
    }
}
