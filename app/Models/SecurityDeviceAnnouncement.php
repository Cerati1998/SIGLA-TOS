<?php

namespace App\Models;

use Database\Factories\SecurityDeviceAnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * An announcement of the entry of lots (H37): the reception of a dispatch of a provider, for one shipping line, with the
 * lots it brings.
 *
 * @property int $id
 * @property int $shipping_line_id
 * @property int $provider_id
 * @property Carbon $received_at
 * @property string $dispatch_number
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ShippingLine $shippingLine
 * @property-read Provider $provider
 * @property-read User $creator
 * @property-read Collection<int, SecurityDeviceLot> $lots
 */
#[Fillable(['shipping_line_id', 'provider_id', 'received_at', 'dispatch_number', 'created_by'])]
class SecurityDeviceAnnouncement extends Model
{
    /** @use HasFactory<SecurityDeviceAnnouncementFactory> */
    use HasFactory;

    /**
     * The most devices an announcement can bring, adding up all its lots (H37, criterion 6).
     */
    public const int MAX_DEVICES = 10000;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'received_at' => 'date',
        ];
    }

    /**
     * Get the number of the announcement shown on screen, as in AN-000004. It is not a column: it comes from the id.
     */
    public function number(): string
    {
        return self::numberFor($this->id);
    }

    /**
     * Get the number shown on screen for the announcement with the given id.
     */
    public static function numberFor(int $id): string
    {
        return sprintf('AN-%06d', $id);
    }

    /**
     * Get the shipping line all the lots of the announcement belong to.
     *
     * @return BelongsTo<ShippingLine, $this>
     */
    public function shippingLine(): BelongsTo
    {
        return $this->belongsTo(ShippingLine::class)->withTrashed();
    }

    /**
     * Get the provider that sent the lots.
     *
     * @return BelongsTo<Provider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class)->withTrashed();
    }

    /**
     * Get the user who entered the announcement.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Get the lots of the announcement, in the order they were entered.
     *
     * @return HasMany<SecurityDeviceLot, $this>
     */
    public function lots(): HasMany
    {
        return $this->hasMany(SecurityDeviceLot::class)->orderBy('id');
    }
}
