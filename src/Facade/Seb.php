<?php

namespace Smartman\Seb\Facade;

use Illuminate\Support\Facades\Facade;

class Seb extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'seb-bank';
    }
}