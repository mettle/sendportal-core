<?php

use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\Workspace;
use Faker\Generator as Faker;

$factory->define(Subscriber::class, function (Faker $faker) {
    return [
        'workspace_id' => factory(Workspace::class),
        'hash' => $faker->uuid,
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->safeEmail
    ];
});

$factory->state(Subscriber::class, 'segmented', function (Faker $faker) {
    return [];
});

$factory->afterCreatingState(Subscriber::class, 'segmented', function ($subscriber, $faker) {
    $subscriber->segments()->saveMany(factory(\Sendportal\Base\Models\Segment::class, 2)->make());
});
