<?php

/** @var Factory $factory */

use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Models\Template;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Template::class, static function (Faker $faker) {
    return [
        'name' => $faker->word,
        'workspace_id' => factory(Workspace::class),
        'content' => '{{content}}'
    ];
});
