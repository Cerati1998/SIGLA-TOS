<?php

namespace App\Actions\GpsDeviceModels;

use App\Models\GpsDeviceModel;
use Illuminate\Database\Eloquent\Builder;

class SearchGpsDeviceModelOptions
{
    /**
     * How many models a search returns at most.
     */
    public const int LIMIT = 20;

    /**
     * Get the GPS device models to choose from in an asynchronous select (H40): the active ones whose name or brand
     * matches the search, or, to show a value already chosen, the selected one even if it was deactivated.
     *
     * @param  array<int, int|string>  $selected  The ids already chosen, to show their names.
     * @return array<int, array{id: int, name: string, description: string}>
     */
    public function handle(?string $search, array $selected = []): array
    {
        $search = trim((string) $search);

        $query = $selected !== []
            ? GpsDeviceModel::withTrashed()->whereKey(array_map('intval', $selected))
            : GpsDeviceModel::query()->when($search !== '', fn (Builder $query) => $query->where(
                fn (Builder $query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhereHas('brand', fn (Builder $brand) => $brand->where('business_name', 'like', "%{$search}%")),
            ));

        return $query
            ->with('brand')
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (GpsDeviceModel $gpsDeviceModel): array => [
                'id' => $gpsDeviceModel->id,
                'name' => $gpsDeviceModel->trashed() ? "{$gpsDeviceModel->name} (desactivado)" : $gpsDeviceModel->name,
                'description' => $gpsDeviceModel->brand->business_name.' · '.$gpsDeviceModel->category->label(),
            ])
            ->all();
    }
}
