<?php

namespace App\Models;

use Database\Factories\IdentityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['id', 'description'])]
#[Table(keyType: 'string', incrementing: false)]
class Identity extends Model
{
    /** @use HasFactory<IdentityFactory> */
    use HasFactory;

    /**
     * The id of the DNI in SUNAT catalog 06.
     */
    public const string DNI = '1';

    /**
     * The id of the RUC in SUNAT catalog 06.
     */
    public const string RUC = '6';
}
