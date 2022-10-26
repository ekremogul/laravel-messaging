<?php

namespace Ekremogul\LaravelMessaging\Commands;

use Illuminate\Console\Command;

class LaravelMessagingCommand extends Command
{
    public $signature = 'laravel-messaging';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
