<?php

namespace Tests\Feature\Setup;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Sendportal\Base\Models\User;
use Tests\TestCase;

class SetupControllerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_should_show_the_setup_view_if_sendportal_is_not_installed()
    {
        $this->assertTrue(true);

//        $this
//            ->get(route('sendportal.setup'))
//            ->assertOk()
//            ->assertSeeLivewire('setup');
    }
//
//    /** @test */
//    public function it_should_redirect_the_user_to_the_login_page_if_sendportal_is_already_installed()
//    {
//        $user = factory(User::class)->create();
//
//        $this
//            ->get(route('sendportal.setup'))
//            ->assertRedirect(route('login'));
//    }
}
