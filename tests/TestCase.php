<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create the application, stopping before any trait such as RefreshDatabase can reach a real database.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $this->ensureTestsUseTheInMemoryDatabase($app);

        return $app;
    }

    /**
     * A cached configuration ignores phpunit.xml, so the tests would run on the database of the .env file.
     */
    protected function ensureTestsUseTheInMemoryDatabase(Application $app): void
    {
        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException(
                "The tests must run on the in-memory SQLite database, not on [{$connection}]. "
                .'If the configuration is cached, run `php artisan config:clear`.'
            );
        }
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
