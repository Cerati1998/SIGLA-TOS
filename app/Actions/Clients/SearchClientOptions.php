<?php

namespace App\Actions\Clients;

use App\Livewire\Forms\ClientForm;
use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;

class SearchClientOptions
{
    /**
     * How many clients a search returns at most.
     */
    public const int LIMIT = 20;

    /**
     * Get the clients to choose from in an asynchronous select: the active ones whose business name or document matches
     * the search, or, to show a value already chosen, the selected one even if it was deactivated.
     *
     * @param  array<int, int|string>  $selected  The ids already chosen, to show their names.
     * @return array<int, array{id: int, name: string, description: string}>
     */
    public function handle(?string $search, array $selected = []): array
    {
        $search = trim((string) $search);
        $query = $selected !== []
            ? Client::withTrashed()->whereKey(array_map('intval', $selected))
            : Client::query()->when($search !== '', fn (Builder $query) => $query->where(
                fn (Builder $query) => $query
                    ->where('business_name', 'like', '%'.$search.'%')
                    ->orWhere('document_number', 'like', '%'.$search.'%'),
            ));

        return $query
            ->orderBy('business_name')
            ->limit(self::LIMIT)
            ->get(['id', 'identity_id', 'document_number', 'business_name', 'deleted_at'])
            ->map(fn (Client $client): array => [
                'id' => $client->id,
                'name' => $client->trashed() ? "{$client->business_name} (desactivado)" : $client->business_name,
                'description' => (ClientForm::DOCUMENT_TYPES[$client->identity_id]['label'] ?? $client->identity_id).' '.$client->document_number,
            ])
            ->all();
    }
}
