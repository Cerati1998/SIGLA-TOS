<?php

namespace App\Models;

use Database\Factories\KitDispatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * A dispatch of kits to a worker (H15). Who delivers is the user who dispatched them.
 *
 * @property int $id
 * @property int $receiver_id
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Worker $receiver
 * @property-read User $creator
 * @property-read Collection<int, Kit> $kits
 */
#[Fillable(['receiver_id', 'created_by'])]
class KitDispatch extends Model
{
    /** @use HasFactory<KitDispatchFactory> */
    use HasFactory;

    /**
     * Get the worker who received the kits, even if they were deactivated afterwards.
     *
     * @return BelongsTo<Worker, $this>
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'receiver_id')->withTrashed();
    }

    /**
     * Get the user who dispatched the kits, that is, who delivered them.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    /**
     * Get the kits of the dispatch. Each kit is in a single dispatch (criterion 5).
     *
     * @return BelongsToMany<Kit, $this>
     */
    public function kits(): BelongsToMany
    {
        return $this->belongsToMany(Kit::class, 'kit_dispatch_items')->withTimestamps()->orderBy('kits.pouch_code');
    }

    /**
     * Get the number of the dispatch shown on screen, as in D-000013. It is not a column: it comes from the id.
     */
    public function number(): string
    {
        return self::numberFor($this->id);
    }

    /**
     * Get the number shown on screen for the dispatch with the given id.
     */
    public static function numberFor(int $id): string
    {
        return sprintf('D-%06d', $id);
    }

    /**
     * Get the ISO week of the day of the dispatch, in Lima time, as in 2026-W47.
     */
    public function week(): string
    {
        return ($this->created_at ?? now())->timezone('America/Lima')->format('o-\WW');
    }

    /**
     * Get how many kits of each kit type the dispatch has, from the loaded kits, as in
     * "2 Kit de seguridad reefer · 1 Kit de cambio de sellos".
     */
    public function kitTypeSummary(): string
    {
        return self::summarize($this->kits);
    }

    /**
     * Get how many kits of each kit type there are in the given kits, which need their kit type loaded.
     *
     * @param  iterable<int, Kit>  $kits
     */
    public static function summarize(iterable $kits): string
    {
        return collect($kits)
            ->countBy(fn (Kit $kit): string => $kit->kitType->name)
            ->sortKeys()
            ->map(fn (int $count, string $name): string => $count.' '.$name)
            ->join(' · ');
    }
}
