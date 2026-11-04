<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $ruc
 * @property string $business_name
 * @property string|null $trade_name
 * @property string $address
 * @property string $ubigeo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['ruc', 'business_name', 'trade_name', 'address', 'ubigeo'])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;
}
