<?php

namespace App\Actions\Providers;

use App\Livewire\Forms\ProviderForm;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Builder;

class SearchProviderOptions
{
    /**
     * How many providers a search returns at most.
     */
    public const int LIMIT = 20;

    /**
     * Get the providers to choose from in an asynchronous select: the active ones whose business name or document
     * matches the search, or, to show a value already chosen, the selected one even if it was deactivated.
     *
     * @param  array<int, int|string>  $selected  The ids already chosen, to show their names.
     * @return array<int, array{id: int, name: string, description: string}>
     */
    public function handle(?string $search, array $selected = []): array
    {
        $query = $selected !== []
            ? Provider::withTrashed()->whereKey(array_map('intval', $selected))
            : Provider::query()->when(trim((string) $search) !== '', fn (Builder $query) => $query->where(
                fn (Builder $query) => $query
                    ->where('business_name', 'like', '%'.trim((string) $search).'%')
                    ->orWhere('document_number', 'like', '%'.trim((string) $search).'%'),
            ));

        return $query
            ->orderBy('business_name')
            ->limit(self::LIMIT)
            ->get(['id', 'identity_id', 'document_number', 'business_name', 'deleted_at'])
            ->map(fn (Provider $provider): array => [
                'id' => $provider->id,
                'name' => $provider->trashed() ? "{$provider->business_name} (desactivado)" : $provider->business_name,
                'description' => (ProviderForm::DOCUMENT_TYPES[$provider->identity_id]['label'] ?? $provider->identity_id).' '.$provider->document_number,
            ])
            ->all();
    }
}
