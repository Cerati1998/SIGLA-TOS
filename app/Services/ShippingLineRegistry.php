<?php

namespace App\Services;

use App\Models\ShippingLine;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * Square the shipping line that SIGLA 1.0 sends only by its name, such as CHIQUITA, with an active line of the
 * catalog, such as CHQT · Chiquita Brands International (H17, T74). Lines are no longer registered from SIGLA 1.0:
 * when the name does not square with exactly one line, the inspector chooses it, and the choice is remembered in
 * the line (decision of the user of 2026-11-23).
 */
class ShippingLineRegistry
{
    /**
     * Get the active lines that match the name SIGLA 1.0 sends, from the surest way to the loosest: the name already
     * remembered, the exact name or code, the start of the name and any part of it. The first way that finds lines
     * gives them. One line means the name squares with it; more than one, or none, means the inspector chooses.
     *
     * @return Collection<int, ShippingLine>
     */
    public function candidates(?string $sigla1Name): Collection
    {
        $name = $this->normalize($sigla1Name);

        if ($name === '') {
            return new Collection;
        }

        $lowerName = mb_strtolower($name);
        $likeName = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $lowerName);

        /** @var list<Closure(Builder<ShippingLine>): mixed> $ways */
        $ways = [
            fn (Builder $query) => $query->where('sigla1_name', $name),
            fn (Builder $query) => $query->whereRaw('LOWER(name) = ?', [$lowerName])->orWhereRaw('LOWER(code) = ?', [$lowerName]),
            fn (Builder $query) => $query->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$likeName.'%']),
            fn (Builder $query) => $query->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", ['%'.$likeName.'%']),
        ];

        foreach ($ways as $way) {
            $lines = ShippingLine::query()->where($way)->orderBy('name')->get();

            if ($lines->isNotEmpty()) {
                return $lines;
            }
        }

        return new Collection;
    }

    /**
     * Get the only active line that matches the name SIGLA 1.0 sends, or null when none or several match.
     */
    public function match(?string $sigla1Name): ?ShippingLine
    {
        $candidates = $this->candidates($sigla1Name);

        return $candidates->count() === 1 ? $candidates->first() : null;
    }

    /**
     * Remember that SIGLA 1.0 writes this line with this name, so its next requests square with it at once. A name
     * remembered in another line, which can only be a deactivated one, moves to this one.
     */
    public function remember(ShippingLine $shippingLine, ?string $sigla1Name): void
    {
        $name = $this->normalize($sigla1Name);

        if ($name === '' || $shippingLine->sigla1_name === $name) {
            return;
        }

        ShippingLine::withTrashed()
            ->where('sigla1_name', $name)
            ->whereKeyNot($shippingLine->getKey())
            ->update(['sigla1_name' => null]);

        $shippingLine->update(['sigla1_name' => $name]);
    }

    /**
     * Get the name as it is remembered: in upper case and without extra spaces.
     */
    private function normalize(?string $sigla1Name): string
    {
        return mb_strtoupper(Str::squish((string) $sigla1Name));
    }
}
