<?php

namespace Tests\Feature;

use Illuminate\Foundation\Application;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class TestDatabaseGuardTest extends TestCase
{
    public function test_allows_the_mariadb_connection_the_ci_uses(): void
    {
        config(['database.default' => 'mariadb']);

        $this->expectNotToPerformAssertions();

        $this->ensureTestEnvironmentIsActive($this->app);
    }

    public function test_stops_the_tests_outside_the_testing_environment(): void
    {
        $this->app['env'] = 'local';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('protect the real database');

        $this->ensureTestEnvironmentIsActive($this->app);
    }

    public function test_stops_the_tests_when_the_configuration_is_cached(): void
    {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('configurationIsCached')->andReturn(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('protect the real database');

        $this->ensureTestEnvironmentIsActive($app);
    }
}
