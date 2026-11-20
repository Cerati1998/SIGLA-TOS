<?php

namespace App\Actions\Kits;

use App\Enums\KitStatus;
use App\Models\Kit;
use Illuminate\Database\Eloquent\Builder;

class SearchDispatchedKitOptions
{
    /**
     * How many kits a search returns at most.
     */
    public const int LIMIT = 20;

    /**
     * Get the kits a bulk dispatch for the operation can go to (H30, criterion 3): the dispatched kits whose certification
     * has not been closed. Until certifications exist (H17), those are all the kits Despachado. Each one shows its pouch
     * and the container of its certification, and is searched by either of them (T64).
     *
     * @param  array<int, int|string>  $selected  The ids already chosen, to show their names.
     * @return array<int, array{id: int, name: string, description: string}>
     */
    public function handle(?string $search, array $selected = []): array
    {
        $search = mb_strtoupper(trim((string) $search));

        return self::query()
            ->with(['kitType', 'shippingLine', 'dispatches.receiver', 'certification'])
            ->when($selected !== [], fn (Builder $query) => $query->whereKey(array_map('intval', $selected)))
            ->when($selected === [] && $search !== '', fn (Builder $query) => $query->where(fn (Builder $pouchOrContainer) => $pouchOrContainer
                ->where('pouch_code', 'like', '%'.$search.'%')
                ->orWhereHas('certification', fn (Builder $certification) => $certification->where('container', 'like', '%'.$search.'%'))))
            ->orderByDesc('id')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Kit $kit): array => [
                'id' => $kit->id,
                'name' => $kit->pouch_code.' · '.($kit->certification === null ? 'sin contenedor' : $kit->certification->container),
                'description' => $this->description($kit),
            ])
            ->all();
    }

    /**
     * Get the kits that can receive devices of a bulk dispatch for the operation.
     *
     * @return Builder<Kit>
     */
    public static function query(): Builder
    {
        return Kit::query()->where('status', KitStatus::Dispatched);
    }

    /**
     * Describe the kit, as in "Kit de seguridad reefer · MAEU · despachado a Trabajador Uno el 16/11/2026".
     */
    private function description(Kit $kit): string
    {
        $dispatch = $kit->dispatch();
        $description = $kit->kitType->name.' · '.$kit->shippingLine->code;

        if ($dispatch === null) {
            return $description;
        }

        return sprintf('%s · despachado a %s el %s', $description, $dispatch->receiver->full_name, ($dispatch->created_at ?? now())->timezone('America/Lima')->format('d/m/Y'));
    }
}
