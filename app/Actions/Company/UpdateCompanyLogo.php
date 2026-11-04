<?php

namespace App\Actions\Company;

use App\Models\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateCompanyLogo
{
    /**
     * Store the new logo and delete the one it replaces.
     */
    public function handle(Company $company, UploadedFile $logo): void
    {
        $previousPath = $company->logo_path;

        $company->update(['logo_path' => $logo->store('company-logos', 'public')]);

        if ($previousPath !== null) {
            Storage::disk('public')->delete($previousPath);
        }
    }
}
