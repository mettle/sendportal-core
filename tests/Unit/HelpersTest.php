<?php

namespace Tests\Unit;

use Tests\TestCase;

class HelpersTest extends TestCase
{
    /** @test */
    function the_seconds_to_hms_method_correctly_converts_seconds_to_hours_minutes_and_seconds()
    {
        static::assertEquals('01:05:32', secondsToHms(3932));
    }
}
