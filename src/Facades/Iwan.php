<?php

namespace Afaneh262\Iwan\Facades;

use Illuminate\Support\Facades\Facade;

class Iwan extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'iwan';
    }
}
