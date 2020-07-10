<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Email;
use Sendportal\Base\Models\Template;
use Sendportal\Base\Models\User;
use Sendportal\Base\Models\Workspace;
use Tests\TestCase;

class TemplateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function the_template_is_in_use_if_it_has_at_least_one_campaign()
    {
        $template = factory(Template::class)->create();

        $campaign = factory(Campaign::class)->create([
            'template_id' => $template->id
        ]);

        static::assertTrue($template->isInUse());
    }

    /** @test */
    function the_template_is_not_in_use_if_it_has_not_campaigns()
    {
        $template = factory(Template::class)->create();

        static::assertFalse($template->isInUse());
    }
}
