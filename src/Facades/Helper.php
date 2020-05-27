<?php

namespace Sendportal\Base\Facades;

use Illuminate\Support\Facades\Facade;

class Helper extends Facade
{

    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sendportal.helper';
    }
}
