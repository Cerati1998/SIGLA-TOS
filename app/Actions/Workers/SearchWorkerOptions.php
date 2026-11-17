<?php

namespace App\Actions\Workers;

use App\Models\Worker;
use Illuminate\Database\Eloquent\Builder;

class SearchWorkerOptions
{
    /**
     * How many workers a search returns at most.
     */
    public const int LIMIT = 20;

    /**
     * Get the workers to choose from in an asynchronous select: the active ones whose name or document matches the
     * search, or, to show a value already chosen, the selected one.
     *
     * @param  array<int, int|string>  $selected  The ids already chosen, to show their names.
     * @return array<int, array{id: int, name: string, description: string}>
     */
    public function handle(?string $search, array $selected = []): array
    {
        $search = trim((string) $search);

        $query = $selected !== []
            ? Worker::withTrashed()->whereKey(array_map('intval', $selected))
            : Worker::query()->when($search !== '', fn (Builder $query) => $query->where(
                fn (Builder $query) => $query
                    ->where('full_name', 'like', '%'.$search.'%')
                    ->orWhere('document_number', 'like', '%'.$search.'%'),
            ));

        return $query
            ->with('identity')
            ->orderBy('full_name')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Worker $worker): array => [
                'id' => $worker->id,
                'name' => $worker->trashed() ? "{$worker->full_name} (desactivado)" : $worker->full_name,
                'description' => $worker->identity->description.' '.$worker->document_number,
            ])
            ->all();
    }
}
