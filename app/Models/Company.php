<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
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
 * @property string|null $logo_path
 * @property string|null $detraction_account
 * @property string|null $invoice_header
 * @property string|null $invoice_footer
 * @property string|null $sol_user
 * @property string|null $sol_pass
 * @property string|null $client_id
 * @property string|null $client_secret
 * @property string|null $certificate
 * @property bool $production
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'ruc',
    'business_name',
    'trade_name',
    'address',
    'ubigeo',
    'logo_path',
    'detraction_account',
    'invoice_header',
    'invoice_footer',
    'sol_user',
    'sol_pass',
    'client_id',
    'client_secret',
    'certificate',
    'production',
])]
#[Hidden(['sol_pass', 'client_secret', 'certificate'])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sol_pass' => 'encrypted',
            'client_secret' => 'encrypted',
            'certificate' => 'encrypted',
            'production' => 'boolean',
        ];
    }

    /**
     * Determine whether the given credential is stored, without decrypting it.
     */
    public function hasCredential(string $column): bool
    {
        return $this->getRawOriginal($column) !== null;
    }

    /**
     * Determine whether an attribute kept its value. A credential that can no longer be decrypted counts as changed, so that it can be replaced.
     *
     * @param  string  $key
     */
    public function originalIsEquivalent($key): bool
    {
        try {
            return parent::originalIsEquivalent($key);
        } catch (DecryptException) {
            return false;
        }
    }

    /**
     * Get the URL of the logo, if it has one.
     */
    public function logoUrl(): ?string
    {
        return $this->logo_path !== null ? asset('storage/'.$this->logo_path) : null;
    }
}
