<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Sendportal\Base\Models\Template;

$factory->define(Template::class, static function (Faker $faker) {
    return [
        'name' => $faker->word,
        'workspace_id' => \Sendportal\Base\Facades\Sendportal::currentWorkspaceId(),
        'content' => '{{content}}'
    ];
});
