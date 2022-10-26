<?php

namespace Ekremogul\LaravelMessaging\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ekremogul\LaravelMessaging\LaravelMessaging
 */
class LaravelMessaging extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Ekremogul\LaravelMessaging\LaravelMessaging::class;
    }
}
