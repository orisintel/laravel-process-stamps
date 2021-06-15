<?php

namespace AlwaysOpen\ProcessStamps\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use AlwaysOpen\ProcessStamps\ProcessStampsServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * SetUp.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(realpath(__DIR__ . '../migrations/'));
        $this->loadMigrationsFrom(__DIR__ . '/Fakes/migrations/');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ProcessStampsServiceProvider::class,
        ];
    }
}
