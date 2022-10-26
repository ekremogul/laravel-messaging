<?php

namespace Ekremogul\LaravelMessaging;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Ekremogul\LaravelMessaging\Commands\LaravelMessagingCommand;

class LaravelMessagingServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-messaging')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-messaging_table')
            ->hasCommand(LaravelMessagingCommand::class);
    }
}
