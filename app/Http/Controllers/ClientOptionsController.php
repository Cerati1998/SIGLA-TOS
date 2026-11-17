<?php

namespace App\Http\Controllers;

use App\Actions\Clients\SearchClientOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientOptionsController extends Controller
{
    /**
     * Return the clients for the asynchronous select. It is a POST, so that a searched document stays out of the URL.
     */
    public function __invoke(Request $request, SearchClientOptions $searchClientOptions): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'selected' => ['nullable', 'array'],
            'selected.*' => ['integer'],
        ]);

        return response()->json($searchClientOptions->handle($validated['search'] ?? null, $validated['selected'] ?? []));
    }
}
