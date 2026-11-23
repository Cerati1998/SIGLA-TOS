<?php

namespace App\Http\Controllers;

use App\Actions\Workers\SearchWorkerOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkerOptionsController extends Controller
{
    /**
     * Return the workers for the asynchronous select. It is a POST, so that a searched document stays out of the URL.
     */
    public function __invoke(Request $request, SearchWorkerOptions $searchWorkerOptions): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'selected' => ['nullable', 'array'],
            'selected.*' => ['integer'],
        ]);

        return response()->json($searchWorkerOptions->handle($validated['search'] ?? null, $validated['selected'] ?? []));
    }
}
