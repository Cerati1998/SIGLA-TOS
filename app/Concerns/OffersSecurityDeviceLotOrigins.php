<?php

namespace App\Concerns;

use App\Livewire\Forms\SecurityDeviceAnnouncementForm;
use App\Livewire\Forms\SecurityDeviceLotForm;
use App\Models\Provider;
use App\Models\SecurityDeviceType;
use App\Models\ShippingLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;

/**
 * The options of the origin of a lot: the active records, plus the current one of the announcement or the lot being
 * corrected.
 *
 * @property SecurityDeviceAnnouncementForm|SecurityDeviceLotForm $form
 */
trait OffersSecurityDeviceLotOrigins
{
    /**
     * Get the shipping lines to choose from, by code.
     *
     * @return Collection<int, ShippingLine>
     */
    #[Computed]
    public function shippingLines(): Collection
    {
        return ShippingLine::withTrashed()
            ->where(fn (Builder $query) => $this->activeOrCurrent($query, 'shippingLineId'))
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
    }

    /**
     * Get the providers to choose from, by business name.
     *
     * @return Collection<int, Provider>
     */
    #[Computed]
    public function providers(): Collection
    {
        return Provider::withTrashed()
            ->where(fn (Builder $query) => $this->activeOrCurrent($query, 'providerId'))
            ->orderBy('business_name')
            ->get(['id', 'business_name']);
    }

    /**
     * Get the types of security device to choose from, by name (H07, criterion 2), with whether they use a series and
     * require the approval of their lots (T62).
     *
     * @return Collection<int, SecurityDeviceType>
     */
    #[Computed]
    public function securityDeviceTypes(): Collection
    {
        return SecurityDeviceType::withTrashed()
            ->where(fn (Builder $query) => $this->activeOrCurrent($query, 'securityDeviceTypeId'))
            ->orderBy('name')
            ->get(['id', 'name', 'uses_series', 'requires_lot_approval']);
    }

    /**
     * Get the boxes of each type to choose from, by its id, for the form to hide the series and warn in the browser.
     *
     * @return array<int, array{usesSeries: bool, requiresApproval: bool}>
     */
    #[Computed]
    public function securityDeviceTypeBoxes(): array
    {
        return $this->securityDeviceTypes
            ->mapWithKeys(fn (SecurityDeviceType $type): array => [$type->id => ['usesSeries' => $type->uses_series, 'requiresApproval' => $type->requires_lot_approval]])
            ->all();
    }

    /**
     * Keep the active records, and the current one of the lot being corrected.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @param  'shippingLineId'|'providerId'|'securityDeviceTypeId'  $field
     * @return Builder<TModel>
     */
    private function activeOrCurrent(Builder $query, string $field): Builder
    {
        return $query->whereNull('deleted_at')
            ->when($this->form->currentOriginIds[$field] ?? null, fn (Builder $current, int $id) => $current->orWhere('id', $id));
    }
}
