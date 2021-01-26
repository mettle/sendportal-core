<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Template;
use Tests\TestCase;

class TemplateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function the_template_is_in_use_if_it_has_at_least_one_campaign()
    {
        // given
        $template = Template::factory()->create();

        Campaign::factory()->create([
            'template_id' => $template->id
        ]);

        // then
        static::assertTrue($template->isInUse());
    }

    /** @test */
    function the_template_is_not_in_use_if_it_has_not_campaigns()
    {
        // given
        $template = Template::factory()->create();

        // then
        static::assertFalse($template->isInUse());
    }
}
