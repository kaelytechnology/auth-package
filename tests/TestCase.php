<?php

namespace Kaely\AuthPackage\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Kaely\AuthPackage\AuthPackageServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            AuthPackageServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Configurar base de datos para testing
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Configurar Sanctum
        $app['config']->set('sanctum.stateful', []);
        $app['config']->set('sanctum.guard', ['web']);
    }
} 