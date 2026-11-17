<?php

namespace App\Models;

use App\Enums\PaymentCondition;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $identity_id
 * @property string $document_number
 * @property string $business_name
 * @property string|null $address
 * @property string|null $email
 * @property string|null $phone
 * @property PaymentCondition $payment_condition
 * @property int|null $credit_days
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Identity $identity
 */
#[Fillable(['identity_id', 'document_number', 'business_name', 'address', 'email', 'phone', 'payment_condition', 'credit_days'])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The default values of the attributes: a new client pays in cash (H31, criterion 2).
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'payment_condition' => 'cash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_condition' => PaymentCondition::class,
            'credit_days' => 'integer',
        ];
    }

    /**
     * Get the document type of the client.
     *
     * @return BelongsTo<Identity, $this>
     */
    public function identity(): BelongsTo
    {
        return $this->belongsTo(Identity::class);
    }
}
