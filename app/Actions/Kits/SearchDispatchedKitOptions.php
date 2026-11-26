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
     * Get the kits a bulk dispatch for the operation can go to (H30, criterion 3): the kits of the certifications in
     * progress, reopened or closed, never annulled (H38; before, only the kits Used, T64). Each option is that container
     * with its original kit, searched by the container or by the pouch, so choosing a container takes the kit it was
     * assigned. Its devices are the extra devices of that certification (H38).
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
                'name' => $kit->certification?->container.' · '.$kit->pouch_code,
                'description' => $this->description($kit),
            ])
            ->all();
    }

    /**
     * Get the kits that can receive devices of a bulk dispatch for the operation: assigned to a certification that is
     * not annulled, whether it is in progress, reopened or closed (H38).
     *
     * @return Builder<Kit>
     */
    public static function query(): Builder
    {
        return Kit::query()->whereHas('certification');
    }

    /**
     * Describe the kit of the container, as in "Kit de seguridad reefer · MAEU · En curso · despachado a Trabajador Uno
     * el 16/11/2026".
     */
    private function description(Kit $kit): string
    {
        $dispatch = $kit->dispatch();
        $description = $kit->kitType->name.' · '.$kit->shippingLine->code.' · '.$this->stage($kit);

        if ($dispatch === null) {
            return $description;
        }

        return sprintf('%s · despachado a %s el %s', $description, $dispatch->receiver->full_name, ($dispatch->created_at ?? now())->timezone('America/Lima')->format('d/m/Y'));
    }

    /**
     * Say where the certification of the kit is, without working out its photos: closed, reopened or in progress.
     */
    private function stage(Kit $kit): string
    {
        $certification = $kit->certification;

        return match (true) {
            $certification?->isClosed() === true => 'Cerrada',
            $kit->status === KitStatus::Used => 'Reaperturada',
            default => 'En curso',
        };
    }
}
