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
            ->hasMigrations('create_laravel_messaging_messages_table','create_laravel_messaging_conversations_table')
            ->hasCommand(LaravelMessagingCommand::class);
    }
}
