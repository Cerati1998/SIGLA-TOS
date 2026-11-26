<?php

namespace App\Models;

use App\Enums\SecurityDeviceStatus;
use Database\Factories\SecurityDeviceLotFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A lot of an announcement of the entry of lots (H37): one type of security device and how its serial numbers are built.
 *
 * @property int $id
 * @property int $security_device_announcement_id
 * @property int $security_device_type_id
 * @property string $series_prefix
 * @property int $min_digits
 * @property int $series_start
 * @property int $series_end
 * @property string|null $series_suffix
 * @property int $quantity
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SecurityDeviceAnnouncement $announcement
 * @property-read SecurityDeviceType $securityDeviceType
 */
#[Fillable(['security_device_announcement_id', 'security_device_type_id', 'series_prefix', 'min_digits', 'series_start', 'series_end', 'series_suffix', 'quantity', 'notes'])]
class SecurityDeviceLot extends Model
{
    /** @use HasFactory<SecurityDeviceLotFactory> */
    use HasFactory;

    /**
     * The columns that build the serial numbers of the lot.
     */
    public const array SERIES_COLUMNS = ['series_prefix', 'min_digits', 'series_start', 'series_end', 'series_suffix'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_digits' => 'integer',
            'series_start' => 'integer',
            'series_end' => 'integer',
            'quantity' => 'integer',
        ];
    }

    /**
     * Build the serial number of a correlative: the prefix, the correlative padded with zeros up to the minimum of
     * digits, and the suffix, with no separator (H07, criterion 5).
     */
    public function serialNumber(int $correlative): string
    {
        return $this->series_prefix.$this->correlativeWithSuffix($correlative);
    }

    /**
     * Get the correlative of a serial number of this lot, or null when it does not follow its series.
     */
    public function correlativeOf(string $serialNumber): ?int
    {
        $suffix = (string) $this->series_suffix;

        if (! str_starts_with($serialNumber, $this->series_prefix) || ($suffix !== '' && ! str_ends_with($serialNumber, $suffix))) {
            return null;
        }

        $digits = substr($serialNumber, strlen($this->series_prefix), strlen($serialNumber) - strlen($this->series_prefix) - strlen($suffix));

        return $digits !== '' && ctype_digit($digits) ? (int) $digits : null;
    }

    /**
     * Get the range of the lot without its prefix, as in 0000200 – 0000399.
     */
    public function rangeLabel(): string
    {
        return $this->correlativeWithSuffix($this->series_start).' – '.$this->correlativeWithSuffix($this->series_end);
    }

    /**
     * Get the name of the lot, as in GSW 0000200 – 0000399.
     */
    public function name(): string
    {
        return ltrim($this->series_prefix.' '.$this->rangeLabel());
    }

    /**
     * Pad a correlative with zeros up to the minimum of digits and add the suffix.
     */
    private function correlativeWithSuffix(int $correlative): string
    {
        return str_pad((string) $correlative, $this->min_digits, '0', STR_PAD_LEFT).$this->series_suffix;
    }

    /**
     * Get every serial number of the lot, from the first correlative to the last one.
     *
     * @return list<string>
     */
    public function serialNumbers(): array
    {
        return array_map($this->serialNumber(...), range($this->series_start, $this->series_end));
    }

    /**
     * Get the announcement the lot came in.
     *
     * @return BelongsTo<SecurityDeviceAnnouncement, $this>
     */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(SecurityDeviceAnnouncement::class, 'security_device_announcement_id');
    }

    /**
     * Get the type of the devices of the lot.
     *
     * @return BelongsTo<SecurityDeviceType, $this>
     */
    public function securityDeviceType(): BelongsTo
    {
        return $this->belongsTo(SecurityDeviceType::class)->withTrashed();
    }

    /**
     * Get the devices of the lot.
     *
     * @return HasMany<SecurityDevice, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(SecurityDevice::class);
    }

    /**
     * Get the approvals log of the lot, the latest first (H29).
     *
     * @return HasMany<SecurityDeviceLotApproval, $this>
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(SecurityDeviceLotApproval::class)->latest('id');
    }

    /**
     * Check that the series of the lot can still be corrected: none of its devices changed status, so all of them are
     * still as they were born, Inactive or, when its type does not require approval, Available (H28, criterion 3; T62).
     * The same check lets the lot be removed from its announcement (H37, criterion 12).
     */
    public function seriesCanBeCorrected(): bool
    {
        return ! $this->devices()->where('status', '!=', $this->initialDeviceStatus())->exists();
    }

    /**
     * Get the status the devices of the lot were born with, by its type (T62).
     */
    public function initialDeviceStatus(): SecurityDeviceStatus
    {
        return $this->securityDeviceType->initialDeviceStatus();
    }

    /**
     * Check whether the devices of the lot need the approval of their shipping line (H29), by its type (T62).
     */
    public function requiresApproval(): bool
    {
        return $this->securityDeviceType->requires_lot_approval;
    }

    /**
     * Count the devices of the lot in each status, with every status present.
     *
     * @return array<string, int>
     */
    public function deviceCountsByStatus(): array
    {
        $counts = $this->devices()
            ->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(SecurityDeviceStatus::cases())
            ->mapWithKeys(fn (SecurityDeviceStatus $status): array => [$status->value => (int) ($counts[$status->value] ?? 0)])
            ->all();
    }
}
