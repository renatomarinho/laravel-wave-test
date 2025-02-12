<?php

namespace RenatoMarinho\LaravelWaveTest;

use Illuminate\Support\ServiceProvider;
use RenatoMarinho\LaravelWaveTest\Commands\GenerateTestsCommand;
use RenatoMarinho\LaravelWaveTest\Commands\ExecuteTestsCommand;

class LaravelWaveTestServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            GenerateTestsCommand::class,
            ExecuteTestsCommand::class,
        ]);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/test-generator.php' => config_path('test-generator.php'),
            ], 'config');
        }
    }
}
