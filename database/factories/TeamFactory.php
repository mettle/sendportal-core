<?php

/** @var Factory $factory */

use Sendportal\Base\Models\Team;
use Sendportal\Base\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Team::class, static function (Faker $faker) {
    $name = $faker->company;

    return [
        'name' => $name,
        'owner_id' => factory(User::class),
    ];
});

$factory->afterCreating(Team::class, static function (Team $team, Faker $faker) {
    $team->users()->attach($team->owner_id, ['role' => Team::ROLE_OWNER]);
});
