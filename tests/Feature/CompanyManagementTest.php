<?php

namespace Tests\Feature;

use App\Livewire\Company\EditForm;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->company = Company::factory()->create([
            'ruc' => '20474360955',
            'business_name' => 'Empresa Original',
            'trade_name' => 'Original',
            'address' => 'Av. Original 123',
            'ubigeo' => '070101',
        ]);

        $this->actingAs(User::factory()->recycle($this->company)->create());
    }

    public function test_guests_cannot_open_the_screen(): void
    {
        auth()->logout();

        $this->get(route('intranet.company.edit'))->assertRedirect(route('login'));
    }

    public function test_the_screen_shows_the_company_from_the_menu(): void
    {
        $this->get(route('intranet.company.edit'))
            ->assertOk()
            ->assertSee(route('intranet.company.edit'))
            ->assertSee('Empresa Original')
            ->assertSee('20474360955');
    }

    public function test_the_identification_data_can_be_changed(): void
    {
        Livewire::test(EditForm::class)
            ->set('form.ruc', '20123456789')
            ->set('form.businessName', 'Empresa Nueva')
            ->set('form.tradeName', '')
            ->set('form.address', 'Av. Nueva 456')
            ->set('form.ubigeo', '200501')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('toast', icon: 'success', title: 'Empresa actualizada');

        $this->assertDatabaseCount('companies', 1);
        $this->assertDatabaseHas('companies', [
            'ruc' => '20123456789',
            'business_name' => 'Empresa Nueva',
            'trade_name' => null,
            'address' => 'Av. Nueva 456',
            'ubigeo' => '200501',
        ]);
    }

    public function test_the_identification_data_is_validated(): void
    {
        $this->app->setLocale('es');

        Livewire::test(EditForm::class)
            ->set('form.ruc', '123')
            ->set('form.businessName', '')
            ->set('form.address', '')
            ->set('form.ubigeo', 'abc')
            ->call('save')
            ->assertHasErrors([
                'form.ruc' => 'digits',
                'form.businessName' => 'required',
                'form.address' => 'required',
                'form.ubigeo' => 'digits',
            ])
            ->assertSeeText('El campo RUC debe tener 11 dígitos.')
            ->assertNotDispatched('toast');

        $this->assertSame('20474360955', $this->company->refresh()->ruc);
    }

    public function test_the_billing_data_is_optional_and_production_starts_off(): void
    {
        Livewire::test(EditForm::class)
            ->assertSet('form.production', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->company->refresh();

        $this->assertFalse($this->company->production);
        $this->assertNull($this->company->detraction_account);
        $this->assertNull($this->company->sol_pass);
        $this->assertNull($this->company->certificate);
    }

    public function test_the_billing_data_can_be_changed_and_is_shown_again(): void
    {
        Livewire::test(EditForm::class)
            ->set('form.detractionAccount', '00-000-123456')
            ->set('form.invoiceHeader', 'Encabezado de prueba')
            ->set('form.invoiceFooter', 'Pie de prueba')
            ->set('form.solUser', 'USUARIO1')
            ->set('form.clientId', 'client-id-de-prueba')
            ->set('form.production', true)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(EditForm::class)
            ->assertSet('form.detractionAccount', '00-000-123456')
            ->assertSet('form.invoiceHeader', 'Encabezado de prueba')
            ->assertSet('form.invoiceFooter', 'Pie de prueba')
            ->assertSet('form.solUser', 'USUARIO1')
            ->assertSet('form.clientId', 'client-id-de-prueba')
            ->assertSet('form.production', true);
    }

    public function test_the_credentials_are_stored_encrypted(): void
    {
        $certificate = $this->validCertificate();

        Livewire::test(EditForm::class)
            ->set('form.solPass', 'clave-sol-secreta')
            ->set('form.clientSecret', 'client-secret-secreto')
            ->set('form.certificate', UploadedFile::fake()->createWithContent('certificado.pem', $certificate))
            ->call('save')
            ->assertHasNoErrors();

        $stored = DB::table('companies')->where('id', $this->company->id)->first();

        $this->assertSame('clave-sol-secreta', Crypt::decryptString($stored->sol_pass));
        $this->assertSame('client-secret-secreto', Crypt::decryptString($stored->client_secret));
        $this->assertSame($certificate, Crypt::decryptString($stored->certificate));
    }

    public function test_the_credentials_never_reach_the_browser(): void
    {
        $this->company->update([
            'sol_pass' => 'clave-sol-secreta',
            'client_secret' => 'client-secret-secreto',
            'certificate' => 'contenido-del-certificado',
        ]);

        $secrets = ['clave-sol-secreta', 'client-secret-secreto', 'contenido-del-certificado'];

        $this->get(route('intranet.company.edit'))->assertOk()->assertDontSee($secrets);

        Livewire::test(EditForm::class)
            ->assertSet('form.solPass', '')
            ->assertSet('form.clientSecret', '')
            ->assertSet('form.certificate', null)
            ->assertDontSeeHtml($secrets)
            ->assertSeeText('Cargada. Déjala vacía para conservarla.')
            ->assertSeeText('Cargado. Déjalo vacío para conservarlo.');

        $this->assertEmpty(array_intersect_key($this->company->toArray(), array_flip(['sol_pass', 'client_secret', 'certificate'])));
    }

    public function test_the_typed_credentials_are_forgotten_after_saving(): void
    {
        Livewire::test(EditForm::class)
            ->set('form.solPass', 'clave-sol-secreta')
            ->set('form.clientSecret', 'client-secret-secreto')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.solPass', '')
            ->assertSet('form.clientSecret', '')
            ->assertDontSeeHtml(['clave-sol-secreta', 'client-secret-secreto']);
    }

    public function test_empty_credentials_keep_the_stored_values(): void
    {
        $this->company->update([
            'sol_pass' => 'clave-sol-secreta',
            'client_secret' => 'client-secret-secreto',
            'certificate' => 'contenido-del-certificado',
        ]);

        Livewire::test(EditForm::class)->call('save')->assertHasNoErrors();

        $this->company->refresh();

        $this->assertSame('clave-sol-secreta', $this->company->sol_pass);
        $this->assertSame('client-secret-secreto', $this->company->client_secret);
        $this->assertSame('contenido-del-certificado', $this->company->certificate);
    }

    public function test_a_credential_can_be_replaced(): void
    {
        $this->company->update(['sol_pass' => 'clave-anterior']);

        Livewire::test(EditForm::class)
            ->set('form.solPass', 'clave-nueva')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('clave-nueva', $this->company->refresh()->sol_pass);
    }

    public function test_the_uploaded_certificate_does_not_stay_in_the_temporary_folder(): void
    {
        Livewire::test(EditForm::class)
            ->set('form.certificate', UploadedFile::fake()->createWithContent('certificado.pem', $this->validCertificate()))
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.certificate', null);

        foreach (Storage::disk('tmp-for-tests')->allFiles('livewire-tmp') as $temporaryFile) {
            $this->assertStringNotContainsString('PRIVATE KEY', (string) Storage::disk('tmp-for-tests')->get($temporaryFile));
        }
    }

    public function test_the_certificate_must_hold_a_certificate_and_its_key(): void
    {
        $certificateWithoutKey = strstr($this->validCertificate(), '-----BEGIN CERTIFICATE-----');

        foreach (['esto no es un certificado', (string) $certificateWithoutKey] as $content) {
            Livewire::test(EditForm::class)
                ->set('form.certificate', UploadedFile::fake()->createWithContent('certificado.pem', $content))
                ->call('save')
                ->assertHasErrors('form.certificate')
                ->assertSeeText('El archivo debe ser un PEM con el certificado y su clave privada, sin contraseña.');
        }

        $this->assertNull($this->company->refresh()->certificate);
    }

    public function test_a_logo_can_be_uploaded_and_replaces_the_previous_one(): void
    {
        Livewire::test(EditForm::class)
            ->set('form.logo', UploadedFile::fake()->image('logo.png'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('form.logo', null);

        $previousPath = (string) $this->company->refresh()->logo_path;

        Storage::disk('public')->assertExists($previousPath);
        $this->get(route('intranet.company.edit'))->assertSee(asset('storage/'.$previousPath));

        Livewire::test(EditForm::class)
            ->set('form.logo', UploadedFile::fake()->image('logo.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        Storage::disk('public')->assertMissing($previousPath);
        Storage::disk('public')->assertExists((string) $this->company->refresh()->logo_path);
    }

    public function test_the_logo_must_be_a_png_or_jpg_image(): void
    {
        foreach ([UploadedFile::fake()->image('logo.gif'), UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf')] as $file) {
            Livewire::test(EditForm::class)
                ->set('form.logo', $file)
                ->call('save')
                ->assertHasErrors('form.logo');
        }

        $this->assertNull($this->company->refresh()->logo_path);
    }

    public function test_the_screen_still_works_when_a_credential_cannot_be_decrypted(): void
    {
        DB::table('companies')->where('id', $this->company->id)->update(['sol_pass' => 'cifrada-con-otra-clave']);

        Livewire::test(EditForm::class)
            ->assertSeeText('Cargada. Déjala vacía para conservarla.')
            ->set('form.businessName', 'Empresa Nueva')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Empresa Nueva', $this->company->refresh()->business_name);

        Livewire::test(EditForm::class)
            ->set('form.solPass', 'clave-nueva')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('clave-nueva', $this->company->refresh()->sol_pass);
    }

    /**
     * Generate a self-signed certificate followed by its private key, with a minimal OpenSSL configuration.
     */
    private function validCertificate(): string
    {
        $configPath = (string) tempnam(sys_get_temp_dir(), 'openssl');
        file_put_contents($configPath, "[req]\ndistinguished_name = dn\n[dn]\n");
        $config = ['config' => $configPath, 'private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA, 'digest_alg' => 'sha256'];

        $privateKey = openssl_pkey_new($config);
        $this->assertNotFalse($privateKey);

        $request = openssl_csr_new(['commonName' => 'SIGLA'], $privateKey, $config);
        $this->assertNotFalse($request);
        $this->assertNotTrue($request);

        $certificate = openssl_csr_sign($request, null, $privateKey, 1, $config);
        $this->assertNotFalse($certificate);

        openssl_pkey_export($privateKey, $keyPem, null, $config);
        openssl_x509_export($certificate, $certificatePem);
        unlink($configPath);

        return $keyPem.$certificatePem;
    }
}
