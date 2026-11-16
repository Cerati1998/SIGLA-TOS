<?php

namespace App\Models;

use App\Enums\SecurityDeviceKind;
use Database\Factories\SecurityDeviceTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property SecurityDeviceKind $kind
 * @property bool $splits_internal_external
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'kind', 'splits_internal_external'])]
class SecurityDeviceType extends Model
{
    /** @use HasFactory<SecurityDeviceTypeFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => SecurityDeviceKind::class,
            'splits_internal_external' => 'boolean',
        ];
    }
}
