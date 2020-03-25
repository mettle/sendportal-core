<?php

/** @var Factory $factory */

use Sendportal\Base\Models\Invitation;
use Sendportal\Base\Models\Team;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

$factory->define(Invitation::class, static function (Faker $faker) {
    return [
        'id' => Uuid::uuid4(),
        'user_id' => null,
        'team_id' => factory(Team::class),
        'role' => Team::ROLE_MEMBER,
        'email' => $faker->safeEmail,
        'token' => Str::random(40)
    ];
});
