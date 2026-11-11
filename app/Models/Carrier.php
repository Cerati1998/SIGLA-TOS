<?php

namespace App\Models;

use Database\Factories\CarrierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A transport company, identified by its RUC. It does not depend on drivers or vehicles.
 *
 * @property int $id
 * @property string $identity_id
 * @property string $document_number
 * @property string $business_name
 * @property string|null $address
 * @property string|null $email
 * @property string|null $phone
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Identity $identity
 */
#[Fillable(['identity_id', 'document_number', 'business_name', 'address', 'email', 'phone'])]
class Carrier extends Model
{
    /** @use HasFactory<CarrierFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the document type of the carrier, always a RUC.
     *
     * @return BelongsTo<Identity, $this>
     */
    public function identity(): BelongsTo
    {
        return $this->belongsTo(Identity::class);
    }
}
