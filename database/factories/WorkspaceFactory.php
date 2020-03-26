<?php

/** @var Factory $factory */

use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Workspace::class, static function (Faker $faker) {
    $name = $faker->company;

    return [
        'name' => $name,
        'owner_id' => factory(User::class),
    ];
});

$factory->afterCreating(Workspace::class, static function (Workspace $workspace, Faker $faker) {
    $workspace->users()->attach($workspace->owner_id, ['role' => Workspace::ROLE_OWNER]);
});
