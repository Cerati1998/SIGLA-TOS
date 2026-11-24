<?php

namespace Tests\Feature;

use App\Actions\Certifications\CloseCertification;
use App\Actions\Certifications\DescribeCertification;
use App\Actions\Certifications\ReopenCertification;
use App\Enums\BulkDispatchReason;
use App\Enums\CertificationSectionType;
use App\Enums\KitStatus;
use App\Enums\SecurityDeviceStatus;
use App\Livewire\Inspections\ExtraDevices;
use App\Livewire\Inspections\StepForm;
use App\Models\BulkDispatch;
use App\Models\Certification;
use App\Models\CertificationPhoto;
use App\Models\CertificationSection;
use App\Models\CertificationStep;
use App\Models\Kit;
use App\Models\SecurityDevice;
use App\Models\SecurityDeviceLot;
use App\Models\SecurityDeviceReplacement;
use App\Models\SecurityDeviceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * H38: replace a device of the kit, already placed in a section, by an extra device that a bulk dispatch for the
 * operation sent to the unit.
 */
class SecurityDeviceReplacementTest extends TestCase
{
    use RefreshDatabase;

    private User $inspector;

    private Certification $certification;

    private CertificationStep $doors;

    private SecurityDeviceType $containerSeal;

    private SecurityDeviceType $tape;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->setLocale('es');
        Storage::fake('local');
        $this->inspector = User::factory()->create(['name' => 'Inspector Uno']);
        $this->containerSeal = SecurityDeviceType::factory()->create(['name' => 'Precinto de naviera']);
        $this->tape = SecurityDeviceType::factory()->create(['name' => 'Cinta de inspección']);

        $kit = Kit::factory()->create(['pouch_code' => 'KIT-000046', 'status' => KitStatus::Dispatched]);
        $this->certification = Certification::factory()->create(['container' => 'ABCU1234567', 'kit_id' => $kit->id]);
        $this->doors = $this->certification->steps()->create([
            'certification_section_id' => CertificationSection::factory()->create(['name' => 'Puertas cerradas y sello provisional', 'type' => CertificationSectionType::Internal])->id,
            'type' => CertificationSectionType::Internal,
            'position' => 1,
        ]);

        foreach (['MSK0000120' => $this->containerSeal, 'CTA0001210' => $this->tape] as $serialNumber => $type) {
            $device = $this->device($serialNumber, $type, SecurityDeviceStatus::InKit);
            $kit->devices()->attach($device->id);
            $this->doors->devices()->attach($device->id);
        }

        CertificationPhoto::factory()->create(['certification_step_id' => $this->doors->id, 'uploaded_by' => $this->inspector->id]);
    }

    public function test_the_inspection_shows_the_extra_devices_and_offers_the_replacement_only_where_there_is_an_unused_extra_of_the_type(): void
    {
        $this->stepForm()->assertDontSeeText('Dispositivos extra');

        $this->extras(['MSK0000455' => $this->containerSeal]);

        $this->stepForm()
            ->assertSeeText('Dispositivos extra')
            ->assertSeeText('1 sin usar')
            ->assertSeeHtml("deviceId: {$this->serial('MSK0000120')->id}")
            ->assertDontSeeHtml("deviceId: {$this->serial('CTA0001210')->id}");

        Livewire::actingAs($this->inspector)
            ->test(ExtraDevices::class, ['certification' => $this->certification, 'type' => CertificationSectionType::Internal])
            ->assertSee('MSK0000455')
            ->assertSee('DG-'.str_pad((string) BulkDispatch::query()->sole()->id, 6, '0', STR_PAD_LEFT));
    }

    public function test_a_device_is_replaced_by_an_extra_of_the_same_type_with_its_reason_and_the_photo_of_the_discard(): void
    {
        $this->extras(['MSK0000455' => $this->containerSeal]);

        $this->replace('MSK0000120', 'MSK0000455', ' Se rompió el precinto al reabrir la puerta. ')
            ->assertHasNoErrors()
            ->assertDispatched('security-device-replaced')
            ->assertDispatched('toast', icon: 'success', title: 'Dispositivo reemplazado');

        $removed = $this->serial('MSK0000120');
        $installed = $this->serial('MSK0000455');
        $replacement = SecurityDeviceReplacement::query()->sole();

        $this->assertSame([$removed->id, $installed->id, $this->doors->id, 'Se rompió el precinto al reabrir la puerta.'], [$replacement->removed_device_id, $replacement->installed_device_id, $replacement->certification_step_id, $replacement->reason]);
        $this->assertTrue($replacement->replacer->is($this->inspector));
        $this->assertEqualsCanonicalizing(['MSK0000455', 'CTA0001210'], $this->doors->devices()->pluck('serial_number')->all());
        $this->assertEqualsCanonicalizing(['MSK0000455', 'CTA0001210'], $this->certification->kit->devices()->pluck('serial_number')->all());
        $this->assertSame(SecurityDeviceStatus::InKit, $installed->status);
        $this->assertSame(SecurityDeviceStatus::Discarded, $removed->status);
        $this->assertSame('Se rompió el precinto al reabrir la puerta.', $removed->discard_reason);
        Storage::disk('local')->assertExists([(string) $removed->discard_photo_path, (string) $removed->discard_thumbnail_path]);
        $this->assertSame($replacement->id, CertificationPhoto::query()->sole()->superseded_by_replacement_id);
    }

    public function test_after_a_replacement_the_section_needs_a_new_photo_before_closing(): void
    {
        $this->extras(['MSK0000455' => $this->containerSeal]);
        $this->assertTrue($this->certification->inspectionsAreComplete());

        $this->replace('MSK0000120', 'MSK0000455')->assertHasNoErrors();

        $this->assertFalse($this->certification->refresh()->inspectionsAreComplete());
        $this->stepForm()->assertSeeText('Falta foto nueva')->assertSeeText('Antes del reemplazo · 1');

        try {
            app(CloseCertification::class)->handle($this->certification, $this->inspector);
            $this->fail('A section without a new photo was closed.');
        } catch (ValidationException) {
            $this->assertNull($this->certification->refresh()->closed_at);
        }

        CertificationPhoto::factory()->create(['certification_step_id' => $this->doors->id, 'uploaded_by' => $this->inspector->id]);
        app(CloseCertification::class)->handle($this->certification->refresh(), $this->inspector);

        $this->assertSame(SecurityDeviceStatus::Used, $this->serial('MSK0000455')->status);
        $this->assertSame(SecurityDeviceStatus::Discarded, $this->serial('MSK0000120')->status);
    }

    public function test_the_extra_must_be_an_unused_extra_of_this_unit_and_of_the_same_type(): void
    {
        $this->extras(['MSK0000455' => $this->containerSeal, 'MSK0000456' => $this->containerSeal, 'CTA0001290' => $this->tape]);
        $sold = $this->device('MSK0000500', $this->containerSeal, SecurityDeviceStatus::Dispatched);
        BulkDispatch::factory()->create(['reason' => BulkDispatchReason::Sale])->devices()->attach($sold->id);

        $this->replace('MSK0000120', 'CTA0001290')->assertDispatched('toast', icon: 'warning', title: 'No se reemplazó', text: 'CTA0001290 es de otro tipo: el extra debe ser del mismo tipo que el dispositivo que sale.');
        $this->replace('MSK0000120', 'MSK0000500')->assertDispatched('toast', icon: 'warning', text: 'MSK0000500 no es un dispositivo extra de esta unidad.');

        $this->replace('MSK0000120', 'MSK0000455')->assertHasNoErrors();
        $this->replace('MSK0000455', 'MSK0000455')->assertDispatched('toast', icon: 'warning', text: 'MSK0000455 ya reemplazó a otro dispositivo. Elige otro extra.');
        $this->replace('MSK0000120', 'MSK0000456')->assertHasErrors('form.removedDeviceId')->assertSeeText('Ese dispositivo no está en una sección de esta inspección.');

        $this->assertSame(1, SecurityDeviceReplacement::query()->count());
        $this->assertSame(SecurityDeviceStatus::Dispatched, $this->serial('MSK0000456')->status);
    }

    public function test_a_closed_certification_is_reopened_first_and_an_annulled_one_takes_no_replacement(): void
    {
        $this->extras(['MSK0000455' => $this->containerSeal]);
        app(CloseCertification::class)->handle($this->certification, $this->inspector);

        $this->replace('MSK0000120', 'MSK0000455')->assertDispatched('toast', icon: 'warning', text: 'La certificación está cerrada. Para reemplazar un dispositivo, reapertúrala desde Asignaciones.');
        $this->assertSame(0, SecurityDeviceReplacement::query()->count());

        app(ReopenCertification::class)->handle($this->certification->refresh(), 'Precinto roto', $this->inspector);
        $this->replace('MSK0000120', 'MSK0000455')->assertHasNoErrors();
        $this->assertSame(SecurityDeviceStatus::Used, $this->serial('MSK0000455')->status);

        $this->certification->refresh()->update(['annulled_at' => now()]);
        $this->extras(['MSK0000457' => $this->containerSeal]);
        $this->replace('MSK0000455', 'MSK0000457')->assertDispatched('toast', icon: 'warning', text: 'Se anuló la asignación de su kit: ya no admite reemplazos.');
        $this->assertSame(1, SecurityDeviceReplacement::query()->count());
    }

    public function test_both_devices_the_reason_and_the_photo_are_required(): void
    {
        $this->extras(['MSK0000455' => $this->containerSeal]);

        Livewire::actingAs($this->inspector)
            ->test(ExtraDevices::class, ['certification' => $this->certification, 'type' => CertificationSectionType::Internal])
            ->set('form.openModal', true)
            ->call('replace')
            ->assertHasErrors(['form.removedDeviceId' => 'required', 'form.installedDeviceId' => 'required', 'form.reason' => 'required', 'form.photo' => 'required'])
            ->assertSeeText('Toma la foto del dispositivo que sale.');

        $this->assertSame(0, SecurityDeviceReplacement::query()->count());
        Storage::disk('local')->assertDirectoryEmpty('security-devices');
    }

    public function test_the_detail_lists_the_replaced_devices_and_the_pdf_shows_only_the_new_one_in_its_section(): void
    {
        $this->extras(['MSK0000455' => $this->containerSeal]);
        $this->replace('MSK0000120', 'MSK0000455', 'Se rompió el precinto al reabrir la puerta.')->assertHasNoErrors();

        $this->actingAs($this->inspector)
            ->get(route('intranet.certifications.show', $this->certification))
            ->assertOk()
            ->assertSeeText('Dispositivos reemplazados')
            ->assertSeeInOrder(['MSK0000120', 'MSK0000455', 'Se rompió el precinto al reabrir la puerta.', 'Inspector Uno'])
            ->assertSeeText('Antes del reemplazo · 1, ya no cuentan')
            ->assertSee(route('intranet.security-devices.discard-photo', $this->serial('MSK0000120')));

        $html = view('pdf.certifications.certification', [
            ...app(DescribeCertification::class)->handle($this->certification->refresh()),
            'certification' => $this->certification,
            'photoData' => fn (): string => '',
            'generatedBy' => 'Inspector Uno',
        ])->render();

        $this->assertStringContainsString('MSK0000455', $html);
        $this->assertStringNotContainsString('MSK0000120', $html);
        $this->assertStringNotContainsString('Dispositivos reemplazados', $html);
    }

    /**
     * Send the given devices to the unit in a bulk dispatch for the operation: its extra devices.
     *
     * @param  array<string, SecurityDeviceType>  $devices
     */
    private function extras(array $devices): void
    {
        $dispatch = BulkDispatch::factory()->create(['reason' => BulkDispatchReason::Operation, 'client_id' => null, 'kit_id' => $this->certification->kit_id]);

        foreach ($devices as $serialNumber => $type) {
            $dispatch->devices()->attach($this->device($serialNumber, $type, SecurityDeviceStatus::Dispatched)->id);
        }
    }

    /**
     * Replace a device by an extra in the window of the extra devices, with a reason and a photo.
     */
    private function replace(string $removed, string $installed, string $reason = 'Se rompió al colocarlo.'): Testable
    {
        return Livewire::actingAs($this->inspector)
            ->test(ExtraDevices::class, ['certification' => $this->certification->refresh(), 'type' => CertificationSectionType::Internal])
            ->set('form.openModal', true)
            ->set('form.removedDeviceId', $this->serial($removed)->id)
            ->set('form.installedDeviceId', $this->serial($installed)->id)
            ->set('form.reason', $reason)
            ->set('form.photo', UploadedFile::fake()->image('foto.jpg'))
            ->set('form.thumbnail', UploadedFile::fake()->image('miniatura.jpg'))
            ->call('replace');
    }

    private function stepForm(): Testable
    {
        return Livewire::actingAs($this->inspector)->test(StepForm::class, ['certification' => $this->certification->refresh(), 'type' => CertificationSectionType::Internal]);
    }

    private function device(string $serialNumber, SecurityDeviceType $type, SecurityDeviceStatus $status): SecurityDevice
    {
        $lot = SecurityDeviceLot::factory()->for($type)->create();

        return SecurityDevice::factory()->for($lot, 'lot')->create(['serial_number' => $serialNumber, 'status' => $status]);
    }

    private function serial(string $serialNumber): SecurityDevice
    {
        return SecurityDevice::query()->where('serial_number', $serialNumber)->sole();
    }
}
