<?php

namespace App\Models;

use Database\Factories\WorkerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A person who receives the kits: an own worker, or one of a provider.
 * It is not linked to a user: the inspector is recognized by the same document type and number (H25, criterion 6).
 *
 * @property int $id
 * @property string $identity_id
 * @property string $document_number
 * @property string $full_name
 * @property string|null $position
 * @property bool $is_own
 * @property int|null $provider_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Identity $identity
 * @property-read Provider|null $provider
 */
#[Fillable(['identity_id', 'document_number', 'full_name', 'position', 'is_own', 'provider_id'])]
class Worker extends Model
{
    /** @use HasFactory<WorkerFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_own' => 'boolean',
        ];
    }

    /**
     * Get the document type of the worker.
     *
     * @return BelongsTo<Identity, $this>
     */
    public function identity(): BelongsTo
    {
        return $this->belongsTo(Identity::class);
    }

    /**
     * Get the provider of the worker, even if it was deactivated afterwards.
     *
     * @return BelongsTo<Provider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class)->withTrashed();
    }
}
