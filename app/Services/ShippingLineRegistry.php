<?php

namespace App\Services;

use App\Models\ShippingLine;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Find a shipping line that SIGLA 1.0 sends only by its name, or register it in the catalog (H17). The code of a new
 * line is made from its name, so the admin can fix it later in Líneas navieras (decision of the user of 2026-11-17).
 */
class ShippingLineRegistry
{
    /**
     * How many characters of the name make the code of a new line.
     */
    private const CODE_LENGTH = 4;

    /**
     * Find the line with this name without minding upper or lower case, or null when it is not in the catalog. A
     * deactivated line counts too.
     */
    public function find(string $name): ?ShippingLine
    {
        $name = $this->normalizeName($name);

        if ($name === '') {
            return null;
        }

        return ShippingLine::withTrashed()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->orderBy('id')
            ->first();
    }

    /**
     * Get the line with this name, reactivating it when it was deactivated, or register it with a code made from
     * its name.
     *
     * @throws InvalidArgumentException When SIGLA 1.0 sends no name.
     */
    public function findOrRegister(string $name): ShippingLine
    {
        $name = $this->normalizeName($name);

        if ($name === '') {
            throw new InvalidArgumentException('SIGLA 1.0 did not send the name of the shipping line.');
        }

        $shippingLine = $this->find($name);

        if ($shippingLine?->trashed()) {
            $shippingLine->restore();
        }

        return $shippingLine ?? ShippingLine::query()->create([
            'code' => $this->codeFor($name),
            'name' => $name,
        ]);
    }

    /**
     * Make the code of a new line: the first four letters or digits of its name in upper case, without spaces or
     * signs (SEATRADE gives SEAT). When that code is taken, a number is added: MAER1, MAER2 and so on.
     */
    public function codeFor(string $name): string
    {
        $base = Str::of(Str::ascii($name))->upper()->replaceMatches('/[^A-Z0-9]/', '')->substr(0, self::CODE_LENGTH)->value();
        $base = $base === '' ? 'NAV' : $base;
        $code = $base;

        for ($number = 1; $this->codeIsTaken($code); $number++) {
            $code = $base.$number;
        }

        return $code;
    }

    private function codeIsTaken(string $code): bool
    {
        return ShippingLine::withTrashed()->where('code', $code)->exists();
    }

    private function normalizeName(string $name): string
    {
        return Str::squish($name);
    }
}
