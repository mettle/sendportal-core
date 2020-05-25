<?php

use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;
use Sendportal\Base\Models\Workspace;
use Faker\Generator as Faker;

$factory->define(EmailService::class, function (Faker $faker)
{
    return [
        'name' => ucwords($faker->word),
        'workspace_id' => factory(Workspace::class),
        'type_id' => $faker->randomElement(EmailServiceType::pluck('id')),
        'settings' => ['foo' => 'bar'],
    ];
});
