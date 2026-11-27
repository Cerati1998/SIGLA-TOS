<?php

namespace App\Http\Controllers;

use App\Actions\GpsDeviceModels\SearchGpsDeviceModelOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GpsDeviceModelOptionsController extends Controller
{
    /**
     * Return the GPS device models for the asynchronous select (H40). It is a POST, like the other selects.
     */
    public function __invoke(Request $request, SearchGpsDeviceModelOptions $searchGpsDeviceModelOptions): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'selected' => ['nullable', 'array'],
            'selected.*' => ['integer'],
        ]);

        return response()->json($searchGpsDeviceModelOptions->handle($validated['search'] ?? null, $validated['selected'] ?? []));
    }
}
