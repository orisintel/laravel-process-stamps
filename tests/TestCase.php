<?php

namespace OrisIntel\ProcessStamps\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use OrisIntel\ProcessStamps\ProcessStampsServiceProvider;

abstract class TestCase extends Orchestra
{
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

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
    }
}
