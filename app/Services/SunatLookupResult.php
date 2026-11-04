<?php

namespace App\Services;

readonly class SunatLookupResult
{
    /**
     * @param  array<string, string|null>  $data  The normalized fields of the document, empty unless it was found.
     */
    public function __construct(
        public SunatLookupStatus $status,
        public array $data = [],
    ) {}
}
