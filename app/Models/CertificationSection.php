<?php

namespace App\Models;

use App\Enums\CertificationSectionType;
use Database\Factories\CertificationSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A section of the container that the certification inspects (H11). Its order is the order of the steps.
 *
 * @property int $id
 * @property string $name
 * @property CertificationSectionType $type
 * @property int|null $sort_order Position among the active sections; null while it is deactivated.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'type', 'sort_order'])]
class CertificationSection extends Model
{
    /** @use HasFactory<CertificationSectionFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CertificationSectionType::class,
            'sort_order' => 'integer',
        ];
    }
}
