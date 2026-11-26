<?php

namespace App\Models;

use App\Enums\BulkDispatchReason;
use Database\Factories\BulkDispatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * A dispatch of loose security devices to a worker, without a kit (H30). Who delivers is the user who dispatched
 * them. With a sale the devices go to a client; with the operation, to a kit already dispatched.
 *
 * @property int $id
 * @property int $receiver_id
 * @property int $created_by
 * @property BulkDispatchReason $reason
 * @property int|null $client_id
 * @property int|null $kit_id
 * @property int $yard_zone_id
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Worker $receiver
 * @property-read User $creator
 * @property-read Client|null $client
 * @property-read Kit|null $kit
 * @property-read YardZone $yardZone
 * @property-read Collection<int, SecurityDevice> $devices
 */
#[Fillable(['receiver_id', 'created_by', 'reason', 'client_id', 'kit_id', 'yard_zone_id', 'notes'])]
class BulkDispatch extends Model
{
    /** @use HasFactory<BulkDispatchFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reason' => BulkDispatchReason::class,
        ];
    }

    /**
     * Get the worker who received the devices, even if they were deactivated afterwards.
     *
     * @return BelongsTo<Worker, $this>
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'receiver_id')->withTrashed();
    }

    /**
     * Get the user who dispatched the devices, that is, who delivered them.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Get the client of a sale, even if it was deactivated afterwards.
     *
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    /**
     * Get the kit dispatched that the devices of the operation go to.
     *
     * @return BelongsTo<Kit, $this>
     */
    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class);
    }

    /**
     * Get the yard zone the devices go to, even if it was deactivated afterwards.
     *
     * @return BelongsTo<YardZone, $this>
     */
    public function yardZone(): BelongsTo
    {
        return $this->belongsTo(YardZone::class)->withTrashed();
    }

    /**
     * Get the devices of the dispatch. Each device is in a single bulk dispatch (criterion 10).
     *
     * @return BelongsToMany<SecurityDevice, $this>
     */
    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(SecurityDevice::class, 'bulk_dispatch_items')->withTimestamps();
    }

    /**
     * Get the ids of the devices of the dispatch.
     *
     * @return list<int>
     */
    public function deviceIds(): array
    {
        return array_values(array_map(intval(...), $this->devices()->pluck('security_devices.id')->all()));
    }

    /**
     * Get the number of the dispatch shown on screen, as in DG-000004. It is not a column: it comes from the id.
     */
    public function number(): string
    {
        return self::numberFor($this->id);
    }

    /**
     * Get the number shown on screen for the bulk dispatch with the given id.
     */
    public static function numberFor(int $id): string
    {
        return sprintf('DG-%06d', $id);
    }

    /**
     * Get the id a search like DG-000004 or 4 points to, or 0 when the search is not a number.
     */
    public static function idFromNumber(string $search): int
    {
        $digits = preg_replace('/^\s*DG-?/i', '', trim($search)) ?? '';

        return ctype_digit($digits) ? (int) $digits : 0;
    }

    /**
     * Get the ISO week of the day of the dispatch, in Lima time, as in 2026-W47.
     */
    public function week(): string
    {
        return ($this->created_at ?? now())->timezone('America/Lima')->format('o-\WW');
    }

    /**
     * Get who or what the devices go to: the client of a sale or, for the operation, the container of the assignment
     * with its kit, as in "MSKU1234567 · KIT-000046" (T87). A kit without an assignment shows only its pouch.
     */
    public function destination(): string
    {
        $container = $this->kit?->certification?->container;

        return match ($this->reason) {
            BulkDispatchReason::Sale => (string) $this->client?->business_name,
            BulkDispatchReason::Operation => $container === null ? 'Kit '.$this->kit?->pouch_code : $container.' · '.$this->kit->pouch_code,
        };
    }

    /**
     * Get how many devices of each type the dispatch has, as in "1 Precinto de naviera · 2 Sello botella", counted in
     * the database.
     */
    public function typeSummary(): string
    {
        return DB::table('bulk_dispatch_items')
            ->join('security_devices', 'security_devices.id', '=', 'bulk_dispatch_items.security_device_id')
            ->join('security_device_lots', 'security_device_lots.id', '=', 'security_devices.security_device_lot_id')
            ->join('security_device_types', 'security_device_types.id', '=', 'security_device_lots.security_device_type_id')
            ->where('bulk_dispatch_items.bulk_dispatch_id', $this->id)
            ->groupBy('security_device_types.name')
            ->orderBy('security_device_types.name')
            ->selectRaw('security_device_types.name as type, count(*) as total')
            ->get()
            ->map(fn (object $row): string => $row->total.' '.$row->type)
            ->join(' · ');
    }
}
