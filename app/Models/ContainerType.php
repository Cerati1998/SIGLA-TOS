<?php

namespace App\Models;

use Database\Factories\ContainerTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * A container type, such as 40RF (40' reefer high cube). The list is loaded once by its seeder and has no screen.
 *
 * @property int $id
 * @property string $code
 * @property string $iso_code
 * @property string $description
 * @property bool $is_reefer
 * @property string $length
 * @property string $width
 * @property string $height
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['code', 'iso_code', 'description', 'is_reefer', 'length', 'width', 'height'])]
class ContainerType extends Model
{
    /** @use HasFactory<ContainerTypeFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_reefer' => 'boolean',
            'length' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
        ];
    }

    /**
     * Get the name shown on screen: the code and the description, such as «40RF · 40'-RFHC Reefer High Cube».
     */
    public function label(): string
    {
        return "{$this->code} · {$this->description}";
    }
}
