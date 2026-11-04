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

        $this->ensureTestEnvironmentIsActive($app);

        return $app;
    }

    /**
     * A cached configuration ignores phpunit.xml, so the tests would run on the database of the .env file.
     * The connection itself is not checked because the CI runs them on MariaDB on purpose.
     */
    protected function ensureTestEnvironmentIsActive(Application $app): void
    {
        if ($app->configurationIsCached() || ! $app->environment('testing')) {
            throw new RuntimeException(
                'The tests stopped to protect the real database: the configuration is cached or the environment is not [testing]. '
                .'Run `php artisan config:clear`.'
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
