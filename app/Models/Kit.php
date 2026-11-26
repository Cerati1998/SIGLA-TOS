<?php

namespace App\Models;

use App\Actions\Kits\AddSecurityDeviceToKit;
use App\Enums\KitAssignment;
use App\Enums\KitStatus;
use App\Enums\SecurityDeviceKind;
use Database\Factories\KitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * A kit of security devices that the warehouse builds by scanning, in a pouch with its own barcode (H14).
 *
 * @property int $id
 * @property string $pouch_code
 * @property int $kit_type_id
 * @property int $shipping_line_id
 * @property KitStatus $status
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read KitType $kitType
 * @property-read ShippingLine $shippingLine
 * @property-read User $creator
 * @property-read Collection<int, SecurityDeviceType> $securityDeviceTypes
 * @property-read Collection<int, SecurityDevice> $devices
 * @property-read Collection<int, KitDispatch> $dispatches
 */
#[Fillable(['pouch_code', 'kit_type_id', 'shipping_line_id', 'status', 'created_by'])]
class Kit extends Model
{
    /** @use HasFactory<KitFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => KitStatus::class,
        ];
    }

    /**
     * Get the kit type the kit was built from, even if it was deactivated.
     *
     * @return BelongsTo<KitType, $this>
     */
    public function kitType(): BelongsTo
    {
        return $this->belongsTo(KitType::class)->withTrashed();
    }

    /**
     * Get the shipping line of the kit, even if it was deactivated: the kit only takes devices of its lots.
     *
     * @return BelongsTo<ShippingLine, $this>
     */
    public function shippingLine(): BelongsTo
    {
        return $this->belongsTo(ShippingLine::class)->withTrashed();
    }

    /**
     * Get the user who created the kit.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Get what the kit carries, copied from its kit type when it was created: editing the kit type does not change
     * the kits built already (H13, criterion 5).
     *
     * @return BelongsToMany<SecurityDeviceType, $this>
     */
    public function securityDeviceTypes(): BelongsToMany
    {
        return $this->belongsToMany(SecurityDeviceType::class)
            ->withPivot('quantity', 'internal_quantity', 'external_quantity')
            ->withTrashed()
            ->orderBy('security_device_types.name');
    }

    /**
     * Get the security devices in the kit, in the order they were added, with the side of the ones split between the
     * internal and the external assignment.
     *
     * @return BelongsToMany<SecurityDevice, $this>
     */
    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(SecurityDevice::class)
            ->withPivot('id', 'assignment')
            ->withTimestamps()
            ->orderBy('kit_security_device.id');
    }

    /**
     * Get the dispatch the kit is in. A kit is in a single dispatch (H15, criterion 5), so it has one at most.
     *
     * @return BelongsToMany<KitDispatch, $this>
     */
    public function dispatches(): BelongsToMany
    {
        return $this->belongsToMany(KitDispatch::class, 'kit_dispatch_items')->withTimestamps();
    }

    /**
     * Get the certification the kit is assigned to, if any: a kit goes in a single certification that is not annulled
     * (H17, criterion 8; H32).
     *
     * @return HasOne<Certification, $this>
     */
    public function certification(): HasOne
    {
        return $this->hasOne(Certification::class)->whereNull('annulled_at');
    }

    /**
     * Get the dispatch the kit is in, or null while it has not been dispatched.
     */
    public function dispatch(): ?KitDispatch
    {
        return $this->dispatches->first();
    }

    /**
     * Get what the kit still lacks, as in "2 Cinta de inspección · Externa y 1 Sello botella", or an empty string when
     * it is complete.
     */
    public function missingDescription(): string
    {
        return collect($this->slots())
            ->filter(fn (array $slot): bool => $slot['added'] < $slot['quantity'])
            ->map(fn (array $slot): string => ($slot['quantity'] - $slot['added']).' '.AddSecurityDeviceToKit::slotLabel($slot))
            ->join(', ', ' y ');
    }

    /**
     * Get the key of the slot of a security device type in a kit: one per type, or one per side when the type is split
     * between the internal and the external assignment.
     */
    public static function slotKey(int $securityDeviceTypeId, ?KitAssignment $assignment): string
    {
        return $securityDeviceTypeId.':'.($assignment === null ? '' : $assignment->value);
    }

    /**
     * Get the security device type of the pouch the kit carries, the one of kind Funda (T76), or null for a kit whose
     * type carried no pouch.
     */
    public function pouchType(): ?SecurityDeviceType
    {
        return $this->securityDeviceTypes->first(fn (SecurityDeviceType $type): bool => $type->kind === SecurityDeviceKind::Pouch);
    }

    /**
     * Get the slots the kit has to fill, the pouch first and then in the order of its device types, each with how many
     * devices it asks for and how many it has. A split type gives the external slot first and then the internal one,
     * leaving out a side that asks for none.
     *
     * @return list<array{key: string, securityDeviceTypeId: int, name: string, assignment: KitAssignment|null, quantity: int, added: int}>
     */
    public function slots(): array
    {
        $added = $this->deviceCountsBySlot();
        $slots = [];

        $securityDeviceTypes = $this->securityDeviceTypes->sortBy(fn (SecurityDeviceType $type): int => $type->kind === SecurityDeviceKind::Pouch ? 0 : 1);

        foreach ($securityDeviceTypes as $securityDeviceType) {
            /** @var object{quantity: int, internal_quantity: int|null, external_quantity: int|null} $pivot */
            $pivot = $securityDeviceType->getRelationValue('pivot');
            $quantities = $pivot->internal_quantity === null && $pivot->external_quantity === null
                ? [[null, (int) $pivot->quantity]]
                : [[KitAssignment::External, (int) $pivot->external_quantity], [KitAssignment::Internal, (int) $pivot->internal_quantity]];

            foreach ($quantities as [$assignment, $quantity]) {
                if ($quantity === 0) {
                    continue;
                }

                $key = self::slotKey($securityDeviceType->id, $assignment);
                $slots[] = [
                    'key' => $key,
                    'securityDeviceTypeId' => $securityDeviceType->id,
                    'name' => $securityDeviceType->name,
                    'assignment' => $assignment,
                    'quantity' => $quantity,
                    'added' => $added[$key] ?? 0,
                ];
            }
        }

        return $slots;
    }

    /**
     * Get how many devices the kit has in each slot, keyed by the slot key.
     *
     * @return array<string, int>
     */
    public function deviceCountsBySlot(): array
    {
        return DB::table('kit_security_device')
            ->join('security_devices', 'security_devices.id', '=', 'kit_security_device.security_device_id')
            ->join('security_device_lots', 'security_device_lots.id', '=', 'security_devices.security_device_lot_id')
            ->where('kit_security_device.kit_id', $this->id)
            ->groupBy('security_device_lots.security_device_type_id', 'kit_security_device.assignment')
            ->get([
                'security_device_lots.security_device_type_id as type_id',
                'kit_security_device.assignment',
                DB::raw('count(*) as total'),
            ])
            ->mapWithKeys(fn (object $row): array => [
                self::slotKey((int) $row->type_id, KitAssignment::tryFrom((string) $row->assignment)) => (int) $row->total,
            ])
            ->all();
    }

    /**
     * Set the status from what the kit has: Armado when every slot is full, Armando while one is missing devices
     * (H14, criterion 14).
     */
    public function syncStatus(): void
    {
        $isComplete = collect($this->slots())->every(fn (array $slot): bool => $slot['added'] >= $slot['quantity']);

        $this->update(['status' => $isComplete ? KitStatus::Built : KitStatus::Building]);
    }

    /**
     * Get the ISO week of the day the kit was created, in Lima time, as in 2026-W47. It is not typed in (H14).
     */
    public function week(): string
    {
        return ($this->created_at ?? now())->timezone('America/Lima')->format('o-\WW');
    }
}
