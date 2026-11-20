<?php

namespace App\Models;

use Database\Factories\CertificationReopeningFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A reopening of a closed certification, with its reason, so its photos can be corrected and it can be closed again
 * (H19, criterion 7). Who reopened it and when stay as its trace, with the closing it undid, so no closing is lost.
 *
 * @property int $id
 * @property int $certification_id
 * @property string $reason
 * @property int $reopened_by
 * @property Carbon $closed_at
 * @property int $closed_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Certification $certification
 * @property-read User $reopener
 * @property-read User $closer
 */
#[Fillable(['certification_id', 'reason', 'reopened_by', 'closed_at', 'closed_by'])]
class CertificationReopening extends Model
{
    /** @use HasFactory<CertificationReopeningFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Get the certification that was reopened.
     *
     * @return BelongsTo<Certification, $this>
     */
    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    /**
     * Get the user who reopened the certification, even if they were deactivated afterwards.
     *
     * @return BelongsTo<User, $this>
     */
    public function reopener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by')->withTrashed();
    }

    /**
     * Get the user who had closed the certification before it was reopened, even if they were deactivated afterwards.
     *
     * @return BelongsTo<User, $this>
     */
    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by')->withTrashed();
    }
}
