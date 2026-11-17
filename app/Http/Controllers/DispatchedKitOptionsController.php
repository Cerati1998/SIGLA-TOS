<?php

namespace App\Http\Controllers;

use App\Actions\Kits\SearchDispatchedKitOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DispatchedKitOptionsController extends Controller
{
    /**
     * Return the dispatched kits for the asynchronous select of a bulk dispatch for the operation (H30).
     */
    public function __invoke(Request $request, SearchDispatchedKitOptions $searchDispatchedKitOptions): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:50'],
            'selected' => ['nullable', 'array'],
            'selected.*' => ['integer'],
        ]);

        return response()->json($searchDispatchedKitOptions->handle($validated['search'] ?? null, $validated['selected'] ?? []));
    }
}
