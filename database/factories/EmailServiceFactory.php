<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;

$factory->define(EmailService::class, function (Faker $faker) {
    return [
        'name' => ucwords($faker->word),
        'workspace_id' => \Sendportal\Base\Facades\Sendportal::currentWorkspaceId(),
        'type_id' => $faker->randomElement(EmailServiceType::pluck('id')),
        'settings' => ['foo' => 'bar'],
    ];
});
