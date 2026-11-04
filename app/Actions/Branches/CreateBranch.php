<?php

namespace App\Actions\Branches;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class CreateBranch
{
    /**
     * Create the branch in the only company of the system, with the next free code.
     *
     * @param  array<string, string|null>  $attributes
     */
    public function handle(array $attributes): Branch
    {
        return DB::transaction(function () use ($attributes): Branch {
            $company = Company::query()->lockForUpdate()->firstOrFail();

            return Branch::query()->create([...$attributes, 'company_id' => $company->id, 'code' => $this->nextCode()]);
        });
    }

    /**
     * Get the number after the highest code, counting the deactivated branches because they keep their code.
     */
    private function nextCode(): string
    {
        $lastNumber = Branch::withTrashed()
            ->pluck('code')
            ->filter(fn ($code): bool => ctype_digit($code))
            ->map(fn ($code): int => (int) $code)
            ->max() ?? 0;

        return str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
    }
}
