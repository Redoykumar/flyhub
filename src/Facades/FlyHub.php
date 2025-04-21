<?php

namespace Redoy\FlyHub\Facades;

use Illuminate\Support\Facades\Facade;

class FlyHub extends Facade
{
    // Binds facade to 'flyhub' service container key
    protected static function getFacadeAccessor()
    {
        return 'flyhub';
    }
}