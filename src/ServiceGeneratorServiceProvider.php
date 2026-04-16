<?php

namespace Zobirofkir\ServiceGenerator;

use Illuminate\Support\ServiceProvider;
use Zobirofkir\ServiceGenerator\Console\MakeServiceCommand;

class ServiceGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeServiceCommand::class,
            ]);
        }
    }
}