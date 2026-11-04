<?php

namespace App\Livewire\Forms;

use App\Actions\Company\UpdateCompanyLogo;
use App\Models\Company;
use App\Rules\PemCertificate;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class CompanyForm extends Form
{
    public string $ruc = '';

    public string $businessName = '';

    public string $tradeName = '';

    public string $address = '';

    public string $ubigeo = '';

    public ?TemporaryUploadedFile $logo = null;

    public string $detractionAccount = '';

    public string $invoiceHeader = '';

    public string $invoiceFooter = '';

    public string $solUser = '';

    public string $solPass = '';

    public string $clientId = '';

    public string $clientSecret = '';

    public ?TemporaryUploadedFile $certificate = null;

    public bool $production = false;

    /**
     * Fill the form with the company. The credentials stay out: the properties of the form reach the browser.
     */
    public function setCompany(Company $company): void
    {
        $this->ruc = $company->ruc;
        $this->businessName = $company->business_name;
        $this->tradeName = (string) $company->trade_name;
        $this->address = $company->address;
        $this->ubigeo = $company->ubigeo;
        $this->detractionAccount = (string) $company->detraction_account;
        $this->invoiceHeader = (string) $company->invoice_header;
        $this->invoiceFooter = (string) $company->invoice_footer;
        $this->solUser = (string) $company->sol_user;
        $this->clientId = (string) $company->client_id;
        $this->production = (bool) $company->production;
    }

    /**
     * Get the validation rules of the form.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'ruc' => ['required', 'digits:11'],
            'businessName' => ['required', 'string', 'max:255'],
            'tradeName' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'ubigeo' => ['required', 'digits:6'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'detractionAccount' => ['nullable', 'string', 'max:255'],
            'invoiceHeader' => ['nullable', 'string', 'max:1000'],
            'invoiceFooter' => ['nullable', 'string', 'max:1000'],
            'solUser' => ['nullable', 'string', 'max:255'],
            'solPass' => ['nullable', 'string', 'max:255'],
            'clientId' => ['nullable', 'string', 'max:255'],
            'clientSecret' => ['nullable', 'string', 'max:255'],
            'certificate' => ['bail', 'nullable', 'file', 'max:32', new PemCertificate],
            'production' => ['boolean'],
        ];
    }

    /**
     * Get the names of the fields in the validation messages.
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'ruc' => 'RUC',
            'businessName' => 'razón social',
            'tradeName' => 'nombre comercial',
            'address' => 'dirección',
            'ubigeo' => 'ubigeo',
            'logo' => 'logo',
            'detractionAccount' => 'cuenta de detracción',
            'invoiceHeader' => 'encabezado de factura',
            'invoiceFooter' => 'pie de factura',
            'solUser' => 'usuario SOL',
            'solPass' => 'clave SOL',
            'clientId' => 'client id',
            'clientSecret' => 'client secret',
            'certificate' => 'certificado',
            'production' => 'modo de producción',
        ];
    }

    /**
     * Save the changes to the company and forget the credentials and the files that were sent.
     */
    public function update(Company $company): void
    {
        $this->validate();

        $company->update([...$this->companyAttributes(), ...$this->newCredentials()]);

        if ($this->logo !== null) {
            (new UpdateCompanyLogo)->handle($company, $this->logo);
        }

        $this->certificate?->delete();

        $this->reset('logo', 'solPass', 'clientSecret', 'certificate');
    }

    /**
     * Get the columns of the company that are always written.
     *
     * @return array<string, bool|string|null>
     */
    private function companyAttributes(): array
    {
        return [
            'ruc' => $this->ruc,
            'business_name' => $this->businessName,
            'trade_name' => $this->tradeName ?: null,
            'address' => $this->address,
            'ubigeo' => $this->ubigeo,
            'detraction_account' => $this->detractionAccount ?: null,
            'invoice_header' => $this->invoiceHeader ?: null,
            'invoice_footer' => $this->invoiceFooter ?: null,
            'sol_user' => $this->solUser ?: null,
            'client_id' => $this->clientId ?: null,
            'production' => $this->production,
        ];
    }

    /**
     * Get the credentials the user filled in. An empty one keeps the stored value.
     *
     * @return array<string, string>
     */
    private function newCredentials(): array
    {
        return array_filter([
            'sol_pass' => $this->solPass,
            'client_secret' => $this->clientSecret,
            'certificate' => (string) $this->certificate?->get(),
        ], fn (string $credential): bool => filled($credential));
    }
}
