<?php

namespace App\Http\Controllers;

use App\Actions\Providers\SearchProviderOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderOptionsController extends Controller
{
    /**
     * Return the providers for the asynchronous select. It is a POST, so that a searched document stays out of the URL.
     */
    public function __invoke(Request $request, SearchProviderOptions $searchProviderOptions): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'selected' => ['nullable', 'array'],
            'selected.*' => ['integer'],
        ]);

        return response()->json($searchProviderOptions->handle($validated['search'] ?? null, $validated['selected'] ?? []));
    }
}
