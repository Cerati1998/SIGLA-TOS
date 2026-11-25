<?php

namespace App\Models;

use App\Enums\DispatchMode;
use Database\Factories\ShippingLineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $sigla1_name How SIGLA 1.0 writes this line, to square its requests with it (T74).
 * @property DispatchMode $dispatch_mode Whether its devices leave in any order or in the order of their correlatives (T82).
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['code', 'name', 'sigla1_name', 'dispatch_mode'])]
class ShippingLine extends Model
{
    /** @use HasFactory<ShippingLineFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The default values of the attributes, as in the table.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'dispatch_mode' => 'random',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dispatch_mode' => DispatchMode::class,
        ];
    }
}
