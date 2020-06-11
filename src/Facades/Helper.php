<?php

namespace Sendportal\Base\Facades;

use Illuminate\Support\Facades\Facade;
use Sendportal\Base\Models\Workspace;

/**
 * Class Helper.
 *
 * @method static mixed displayDate($date, string $timezone = null)
 * @method static Workspace|null getCurrentWorkspace()
 *
 * @see \Sendportal\Base\Services\Helper
 */

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
