<?php

namespace Tests\Feature;

use RuntimeException;
use Tests\TestCase;

class TestDatabaseGuardTest extends TestCase
{
    public function test_allows_the_in_memory_sqlite_database(): void
    {
        $this->ensureTestsUseTheInMemoryDatabase($this->app);

        $this->assertSame('sqlite', config('database.default'));
    }

    public function test_stops_the_tests_on_another_connection(): void
    {
        config(['database.default' => 'mysql']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not on [mysql]');

        $this->ensureTestsUseTheInMemoryDatabase($this->app);
    }

    public function test_stops_the_tests_on_a_sqlite_file(): void
    {
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);

        $this->expectException(RuntimeException::class);

        $this->ensureTestsUseTheInMemoryDatabase($this->app);
    }
}
