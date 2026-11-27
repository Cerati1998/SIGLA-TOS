<?php

namespace Tests\Feature;

use App\Enums\GpsConnectivity;
use App\Livewire\GpsDevices\ConnectivityModal;
use App\Livewire\Tables\GpsDevicesTable;
use App\Models\GpsDevice;
use App\Models\User;
use App\Services\Monitoring\MonitoringPlatform;
use App\Services\Monitoring\MonitoringReport;
use Database\Seeders\IdentitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GpsDeviceConnectivityTest extends TestCase
{
    use RefreshDatabase;

    private const string URL = 'https://monitoring.test/xws/wsService';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(IdentitySeeder::class);
        $this->actingAs(User::factory()->create());
        $this->app->setLocale('es');

        config([
            'services.gps_24hm.url' => self::URL,
            'services.gps_24hm.user' => 'usuario.prueba',
            'services.gps_24hm.password' => 'clave<&>prueba',
        ]);
        Http::preventStrayRequests();
        Carbon::setTestNow('2026-11-27 21:00:00');
    }

    public function test_each_device_has_a_connectivity_button_that_opens_its_window_without_asking_24hm(): void
    {
        Http::fake();
        $gpsDevice = GpsDevice::factory()->create(['code' => 'HBR222', 'monitoring_asset_id' => 288855]);

        $this->get(route('intranet.gps-devices.index'))->assertOk()->assertSeeLivewire(ConnectivityModal::class);
        Livewire::test(GpsDevicesTable::class)->assertSeeHtml('title="Conectividad"');

        Livewire::test(ConnectivityModal::class)
            ->dispatch('show-gps-device-connectivity', gpsDeviceId: $gpsDevice->id)
            ->assertSet('openModal', true)
            ->assertSee('Conectividad de HBR222')
            ->assertSee('288855')
            ->assertSee('Sin verificar')
            ->assertSee('Todavía no se consultó a 24HM.');

        Http::assertNothingSent();
    }

    public function test_verifying_saves_the_last_report_and_shows_the_device_connected(): void
    {
        Http::fake([self::URL => Http::response($this->answer('ConsultarActivoPorIdActivo', $this->asset(['FechaHora' => '2026-11-27 20:39:32'])))]);
        $gpsDevice = GpsDevice::factory()->create(['code' => 'HBR222', 'monitoring_asset_id' => 288855]);

        Livewire::test(ConnectivityModal::class)
            ->dispatch('show-gps-device-connectivity', gpsDeviceId: $gpsDevice->id)
            ->call('verify')
            ->assertSet('failure', null)
            ->assertDispatched('toast', icon: 'success', title: 'Conectividad verificada')
            ->assertSee('Conectado')
            ->assertSee('Avenida LL, Paita, Piura')
            ->assertSee('4.50 V');

        $gpsDevice->refresh();
        $this->assertTrue($gpsDevice->last_reported_at?->equalTo(Carbon::parse('2026-11-27 20:39:32', 'UTC')));
        $this->assertSame('-5.0874634', $gpsDevice->last_latitude);
        $this->assertSame('-81.0859909', $gpsDevice->last_longitude);
        $this->assertSame('Avenida LL, Paita, Piura', $gpsDevice->last_address);
        $this->assertSame('A', $gpsDevice->service_status);
        $this->assertSame('C', $gpsDevice->operational_status);
        $this->assertSame('4.50', $gpsDevice->supply_voltage);
        $this->assertTrue($gpsDevice->last_checked_at?->equalTo(now()));
        $this->assertSame(GpsConnectivity::Connected, $gpsDevice->connectivity());

        Http::assertSent(fn (Request $request): bool => $request->url() === self::URL
            && str_contains($request->body(), '<x:ConsultarActivoPorIdActivo>')
            && str_contains($request->body(), '<IdActivo>288855</IdActivo>')
            && str_contains($request->body(), '<Contrasena>clave&lt;&amp;&gt;prueba</Contrasena>'));
    }

    public function test_a_report_of_24_hours_or_more_is_without_signal(): void
    {
        Http::fake([self::URL => Http::response($this->answer('ConsultarActivoPorIdActivo', $this->asset(['FechaHora' => '2026-09-15 05:21:14'])))]);
        $gpsDevice = GpsDevice::factory()->create(['monitoring_asset_id' => 288855]);

        Livewire::test(ConnectivityModal::class)
            ->dispatch('show-gps-device-connectivity', gpsDeviceId: $gpsDevice->id)
            ->call('verify')
            ->assertSee('Sin señal');

        $this->assertSame(GpsConnectivity::NoSignal, $gpsDevice->refresh()->connectivity());
        $this->assertSame(GpsConnectivity::Connected, tap($gpsDevice)->forceFill(['last_reported_at' => now()->subHours(23)])->connectivity());
        $this->assertSame(GpsConnectivity::NoSignal, tap($gpsDevice)->forceFill(['last_reported_at' => now()->subHours(24)])->connectivity());
    }

    public function test_a_device_without_an_id_of_24hm_cannot_be_verified(): void
    {
        Http::fake();
        $gpsDevice = GpsDevice::factory()->create(['monitoring_asset_id' => null]);

        Livewire::test(ConnectivityModal::class)
            ->dispatch('show-gps-device-connectivity', gpsDeviceId: $gpsDevice->id)
            ->assertSee('Este dispositivo no tiene ID de 24HM.')
            ->call('verify')
            ->assertSet('failure', 'Este dispositivo no tiene ID de 24HM. Agrégalo en su edición para poder verificarlo.');

        Http::assertNothingSent();
    }

    /**
     * @return array<string, array{0: \Closure(): mixed, 1: string}>
     */
    public static function failures(): array
    {
        return [
            'credenciales rechazadas, con su JSON roto' => [fn () => Http::response(self::envelope('ConsultarActivoPorIdActivo', '{"Error":"Usuario no autenticado}')), '24HM rechazó las credenciales.'],
            'activo que 24HM no tiene' => [fn () => Http::response(self::envelope('ConsultarActivoPorIdActivo', '{"ConsultaActivoPorIdActivo":{},"Activos":[]}')), '24HM no tiene el activo 288855.'],
            'sin respuesta' => [fn () => throw new ConnectionException('timeout'), '24HM no respondió. Inténtalo en unos minutos.'],
            'falla SOAP' => [fn () => Http::response('<S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"><S:Body><S:Fault><faultcode>S:Client</faultcode><faultstring>Error</faultstring></S:Fault></S:Body></S:Envelope>', 500), '24HM rechazó la consulta.'],
            'respuesta que no se entiende' => [fn () => Http::response('<html>Mantenimiento</html'), '24HM respondió algo que no se pudo leer.'],
        ];
    }

    /**
     * @param  \Closure(): mixed  $response
     */
    #[DataProvider('failures')]
    public function test_a_failed_check_shows_why_and_keeps_the_report_it_had(\Closure $response, string $message): void
    {
        Http::fake([self::URL => $response]);
        $gpsDevice = GpsDevice::factory()->create([
            'monitoring_asset_id' => 288855,
            'last_reported_at' => '2026-11-27 20:00:00',
            'last_address' => 'Dirección anterior',
        ]);

        Livewire::test(ConnectivityModal::class)
            ->dispatch('show-gps-device-connectivity', gpsDeviceId: $gpsDevice->id)
            ->call('verify')
            ->assertSet('failure', $message)
            ->assertSee('No se pudo verificar: '.$message)
            ->assertNotDispatched('toast');

        $this->assertDatabaseHas('gps_devices', ['id' => $gpsDevice->id, 'last_address' => 'Dirección anterior', 'last_checked_at' => null]);
    }

    public function test_without_credentials_24hm_is_not_asked(): void
    {
        Http::fake();
        config(['services.gps_24hm.password' => null]);
        $gpsDevice = GpsDevice::factory()->create(['monitoring_asset_id' => 288855]);

        Livewire::test(ConnectivityModal::class)
            ->dispatch('show-gps-device-connectivity', gpsDeviceId: $gpsDevice->id)
            ->call('verify')
            ->assertSet('failure', 'Faltan las credenciales de 24HM en la configuración.');

        Http::assertNothingSent();
    }

    public function test_the_check_asks_the_monitoring_platform_in_use_not_only_24hm(): void
    {
        Http::fake();
        $this->app->bind(MonitoringPlatform::class, fn (): MonitoringPlatform => new class implements MonitoringPlatform
        {
            public function asset(int $assetId): MonitoringReport
            {
                return new MonitoringReport($assetId, 'OTRO-1', null, null, Carbon::parse('2026-11-27 20:50:00'), null, null, 'Plataforma de prueba', null, null, null);
            }
        });
        $gpsDevice = GpsDevice::factory()->create(['monitoring_asset_id' => 777]);

        Livewire::test(ConnectivityModal::class)
            ->dispatch('show-gps-device-connectivity', gpsDeviceId: $gpsDevice->id)
            ->call('verify')
            ->assertSee('Plataforma de prueba');

        $this->assertSame(GpsConnectivity::Connected, $gpsDevice->refresh()->connectivity());
        Http::assertNothingSent();
    }

    public function test_a_deactivated_device_can_be_checked_too(): void
    {
        Http::fake([self::URL => Http::response($this->answer('ConsultarActivoPorIdActivo', $this->asset()))]);
        $gpsDevice = GpsDevice::factory()->trashed()->create(['monitoring_asset_id' => 288855]);

        Livewire::test(ConnectivityModal::class)
            ->dispatch('show-gps-device-connectivity', gpsDeviceId: $gpsDevice->id)
            ->call('verify')
            ->assertSet('failure', null);

        $this->assertNotNull($gpsDevice->refresh()->last_checked_at);
    }

    /**
     * An asset as 24HM gives it, with what changes in each test.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function asset(array $overrides = []): array
    {
        return $overrides + [
            'IdActivo' => 288855,
            'Alias' => 'HBR222',
            'Etiqueta' => '',
            'EstadoServicio' => 'A',
            'EstadoOperativo' => 'C',
            'VID' => '2638413',
            'FechaHora' => '2026-11-27 20:39:32',
            'Latitud' => -5.08746337890625,
            'Longitud' => -81.08599090576172,
            'Calle' => 'Avenida LL,,Paita,Piura',
            'IdProducto' => 'BR',
            'IdTipoDispositivo' => 240,
            'VoltajeAlimentacion' => 4.5,
        ];
    }

    /**
     * The answer of an operation with the given asset.
     *
     * @param  array<string, mixed>  $asset
     */
    private function answer(string $operation, array $asset): string
    {
        return self::envelope($operation, (string) json_encode(['ConsultaActivoPorIdActivo' => ['Usuario' => 'usuario.prueba'], 'Activos' => [$asset]]));
    }

    /**
     * Wrap the JSON that 24HM puts in «return» in its SOAP answer.
     */
    private static function envelope(string $operation, string $json): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><S:Envelope xmlns:S="http://schemas.xmlsoap.org/soap/envelope/"><S:Body>'
            ."<ns2:{$operation}Response xmlns:ns2=\"http://x.me.org/\"><return>".htmlspecialchars($json, ENT_XML1)."</return></ns2:{$operation}Response>"
            .'</S:Body></S:Envelope>';
    }
}
