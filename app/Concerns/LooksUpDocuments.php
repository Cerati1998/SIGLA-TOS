<?php

namespace App\Concerns;

use App\Models\Identity;
use App\Services\SunatLookupResult;
use App\Services\SunatLookupStatus;
use App\Services\SunatService;
use Livewire\Attributes\Locked;

/**
 * Look up the document of a form in the external service and fill in the data it finds.
 * The form needs the `identityId` and `documentNumber` properties.
 */
trait LooksUpDocuments
{
    /**
     * How the last lookup of the document ended: `found`, `not_found`, `unavailable` or null when there is no notice to show.
     */
    public ?string $lookupStatus = null;

    /**
     * The document whose data is already in the form, so that it is not looked up again.
     */
    #[Locked]
    public ?string $lookedUpDocument = null;

    /**
     * Get the rules that check the document before looking it up.
     *
     * @return array<string, array<int, mixed>>
     */
    abstract protected function lookupRules(): array;

    /**
     * Put the data the service found in the form.
     */
    abstract protected function fillFromLookup(SunatLookupResult $result): void;

    /**
     * Look up the document and fill in the data the service finds.
     * A document already looked up is skipped unless the lookup is forced with the magnifier button.
     */
    public function lookup(SunatService $sunat, bool $force = false): void
    {
        $this->lookupStatus = null;
        $this->documentNumber = trim($this->documentNumber);

        $this->validate($this->lookupRules(), $this->messages(), $this->validationAttributes());

        if (! $force && $this->lookedUpDocument === $this->documentKey()) {
            return;
        }

        $result = $this->identityId === Identity::RUC
            ? $sunat->lookupRuc($this->documentNumber)
            : $sunat->lookupDni($this->documentNumber);

        $this->lookupStatus = match ($result->status) {
            SunatLookupStatus::Found => 'found',
            SunatLookupStatus::NotFound => 'not_found',
            SunatLookupStatus::Unavailable => 'unavailable',
        };

        if ($result->status === SunatLookupStatus::Unavailable) {
            return;
        }

        $this->rememberLookedUpDocument();

        if ($result->status === SunatLookupStatus::Found) {
            $this->fillFromLookup($result);
        }
    }

    /**
     * Mark the current document as already looked up, as when a saved record is opened for editing.
     */
    protected function rememberLookedUpDocument(): void
    {
        $this->lookedUpDocument = $this->documentKey();
    }

    /**
     * Get the document type and number as one value.
     */
    private function documentKey(): string
    {
        return "{$this->identityId}:{$this->documentNumber}";
    }
}
