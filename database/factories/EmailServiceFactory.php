<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;
use Sendportal\Base\Models\Workspace;

$factory->define(EmailService::class, function (Faker $faker) {
    return [
        'name' => ucwords($faker->word),
        'workspace_id' => factory(Workspace::class),
        'type_id' => $faker->randomElement(EmailServiceType::pluck('id')),
        'settings' => ['foo' => 'bar'],
    ];
});
