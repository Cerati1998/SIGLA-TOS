<?php

namespace App\Models;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['code', 'name', 'sigla1_name'])]
class ShippingLine extends Model
{
    /** @use HasFactory<ShippingLineFactory> */
    use HasFactory, SoftDeletes;
}
