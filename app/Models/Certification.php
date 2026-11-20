<?php

namespace App\Models;

use App\Enums\CertificationSectionType;
use App\Enums\CertificationStatus;
use Database\Factories\CertificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * The certification of a unit, started by an inspector with a gate out request of SIGLA 1.0 and a dispatched kit
 * (H17), which is the assignment of the kit to the unit. It keeps the reference, the booking, the container, the
 * client and the product of the request (the client and the product since T58, so the detail and the PDF do not
 * depend on SIGLA 1.0), and the links to the catalogs; the driver's DNI and license stay in the drivers catalog. Its
 * sections, with the devices of the kit, are its steps, split in the internal and the external inspection (H18). It is
 * closed by hand once both inspections are complete, and it can be reopened with a reason to correct its photos (H19).
 *
 * @property int $id
 * @property string $gate_out_request_reference
 * @property string $booking
 * @property string $container
 * @property string|null $client
 * @property string|null $product
 * @property int $shipping_line_id
 * @property int|null $carrier_id
 * @property int|null $driver_id
 * @property int|null $tractor_id
 * @property int|null $semi_trailer_id
 * @property int $kit_id
 * @property string|null $inspection_sheet_code
 * @property int $yard_zone_id
 * @property int $certified_by
 * @property Carbon $started_at
 * @property Carbon|null $closed_at
 * @property int|null $closed_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ShippingLine $shippingLine
 * @property-read Carrier|null $carrier
 * @property-read Driver|null $driver
 * @property-read Vehicle|null $tractor
 * @property-read Vehicle|null $semiTrailer
 * @property-read Kit $kit
 * @property-read YardZone $yardZone
 * @property-read User $certifier
 * @property-read User|null $closer
 * @property-read Collection<int, CertificationStep> $steps
 * @property-read Collection<int, CertificationReopening> $reopenings
 * @property-read CertificationReopening|null $lastReopening
 */
#[Fillable([
    'gate_out_request_reference', 'booking', 'container', 'client', 'product', 'shipping_line_id', 'carrier_id', 'driver_id', 'tractor_id',
    'semi_trailer_id', 'kit_id', 'inspection_sheet_code', 'yard_zone_id', 'certified_by', 'started_at', 'closed_at', 'closed_by',
])]
class Certification extends Model
{
    /** @use HasFactory<CertificationFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * Get the shipping line of the request, even if it was deactivated afterwards.
     *
     * @return BelongsTo<ShippingLine, $this>
     */
    public function shippingLine(): BelongsTo
    {
        return $this->belongsTo(ShippingLine::class)->withTrashed();
    }

    /**
     * Get the carrier of the request, even if it was deactivated afterwards.
     *
     * @return BelongsTo<Carrier, $this>
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class)->withTrashed();
    }

    /**
     * Get the driver of the request, even if it was deactivated afterwards.
     *
     * @return BelongsTo<Driver, $this>
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class)->withTrashed();
    }

    /**
     * Get the tractor of the request, even if it was deactivated afterwards.
     *
     * @return BelongsTo<Vehicle, $this>
     */
    public function tractor(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'tractor_id')->withTrashed();
    }

    /**
     * Get the semi-trailer of the request, even if it was deactivated afterwards.
     *
     * @return BelongsTo<Vehicle, $this>
     */
    public function semiTrailer(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'semi_trailer_id')->withTrashed();
    }

    /**
     * Get the kit used in the certification.
     *
     * @return BelongsTo<Kit, $this>
     */
    public function kit(): BelongsTo
    {
        return $this->belongsTo(Kit::class);
    }

    /**
     * Get the yard zone where the unit is certified, even if it was deactivated afterwards.
     *
     * @return BelongsTo<YardZone, $this>
     */
    public function yardZone(): BelongsTo
    {
        return $this->belongsTo(YardZone::class)->withTrashed();
    }

    /**
     * Get the inspector who started the certification.
     *
     * @return BelongsTo<User, $this>
     */
    public function certifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certified_by')->withTrashed();
    }

    /**
     * Get the user who closed the certification the last time, even if they were deactivated afterwards.
     *
     * @return BelongsTo<User, $this>
     */
    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by')->withTrashed();
    }

    /**
     * Get the reopenings of the certification, the oldest first.
     *
     * @return HasMany<CertificationReopening, $this>
     */
    public function reopenings(): HasMany
    {
        return $this->hasMany(CertificationReopening::class)->orderBy('created_at')->orderBy('id');
    }

    /**
     * Get the last reopening of the certification, the one shown while it is reopened.
     *
     * @return HasOne<CertificationReopening, $this>
     */
    public function lastReopening(): HasOne
    {
        return $this->hasOne(CertificationReopening::class)->latestOfMany();
    }

    /**
     * Get the sections of the certification, in the order they had in the catalog when the kit was assigned.
     *
     * @return HasMany<CertificationStep, $this>
     */
    public function steps(): HasMany
    {
        return $this->hasMany(CertificationStep::class)->orderBy('position')->orderBy('id');
    }

    /**
     * Whether the certification is closed: then it cannot be edited until it is reopened (H19, criterion 6).
     */
    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }

    /**
     * Load how many sections each inspection has and how many have a photo, and whether it was ever reopened, which
     * the status and the list of assignments read.
     *
     * @param  Builder<Certification>  $query
     */
    #[Scope]
    protected function withInspectionProgress(Builder $query): void
    {
        $query->withCount([
            'steps as internal_total' => fn (Builder $steps) => $steps->where('type', CertificationSectionType::Internal),
            'steps as internal_done' => fn (Builder $steps) => $steps->where('type', CertificationSectionType::Internal)->has('photos'),
            'steps as external_total' => fn (Builder $steps) => $steps->where('type', CertificationSectionType::External),
            'steps as external_done' => fn (Builder $steps) => $steps->where('type', CertificationSectionType::External)->has('photos'),
        ])->withExists('reopenings');
    }

    /**
     * Keep only the certifications in a status, the same one status() works out.
     *
     * @param  Builder<Certification>  $query
     */
    #[Scope]
    protected function whereStatus(Builder $query, CertificationStatus $status): void
    {
        $complete = fn (Builder $certifications) => $certifications
            ->has('steps')
            ->whereDoesntHave('steps', fn (Builder $steps) => $steps->doesntHave('photos'));

        match ($status) {
            CertificationStatus::Closed => $query->whereNotNull('certifications.closed_at'),
            CertificationStatus::Reopened => $query->whereNull('certifications.closed_at')->has('reopenings'),
            CertificationStatus::ReadyToClose => $query->whereNull('certifications.closed_at')->doesntHave('reopenings')->where($complete),
            CertificationStatus::InProgress => $query->whereNull('certifications.closed_at')->doesntHave('reopenings')->whereNot($complete),
        };
    }

    /**
     * Get how far each inspection went, from what withInspectionProgress() loaded, or from the database when it was
     * not loaded.
     *
     * @return array{internal: array{total: int, done: int}, external: array{total: int, done: int}}
     */
    public function progress(): array
    {
        if ($this->getAttribute('internal_total') === null) {
            return [
                'internal' => $this->inspectionProgress(CertificationSectionType::Internal),
                'external' => $this->inspectionProgress(CertificationSectionType::External),
            ];
        }

        return [
            'internal' => ['total' => (int) $this->getAttribute('internal_total'), 'done' => (int) $this->getAttribute('internal_done')],
            'external' => ['total' => (int) $this->getAttribute('external_total'), 'done' => (int) $this->getAttribute('external_done')],
        ];
    }

    /**
     * Whether every section of both inspections has at least one photo, the only way to close it (H19, criteria 1
     * and 2). A certification without sections is never complete.
     */
    public function inspectionsAreComplete(): bool
    {
        $progress = $this->progress();
        $total = $progress['internal']['total'] + $progress['external']['total'];

        return $total > 0 && $progress['internal']['done'] + $progress['external']['done'] === $total;
    }

    /**
     * Get where the certification is: closed, reopened until it is closed again, ready to close or still in progress.
     */
    public function status(): CertificationStatus
    {
        $wasReopened = $this->getAttribute('reopenings_exists') ?? $this->reopenings()->exists();

        return match (true) {
            $this->isClosed() => CertificationStatus::Closed,
            (bool) $wasReopened => CertificationStatus::Reopened,
            $this->inspectionsAreComplete() => CertificationStatus::ReadyToClose,
            default => CertificationStatus::InProgress,
        };
    }

    /**
     * Get how many sections the inspection of a type has and how many of them already have a photo. The inspection
     * is complete when both are the same (H18, criterion 10).
     *
     * @return array{total: int, done: int}
     */
    public function inspectionProgress(CertificationSectionType $type): array
    {
        $steps = $this->steps()->where('type', $type)->withExists('photos')->get();

        return [
            'total' => $steps->count(),
            'done' => $steps->where('photos_exists', true)->count(),
        ];
    }
}
