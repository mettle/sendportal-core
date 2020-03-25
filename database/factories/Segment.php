<?php

use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Team;
use Faker\Generator as Faker;

$factory->define(Segment::class, function (Faker $faker) {
    return [
        'name' => ucwords($faker->unique()->word),
    ];
});

$factory->state(Segment::class, 'subscribed', function (Faker $faker) {
    return [];
});

$factory->afterCreatingState(Segment::class, 'subscribed', function ($subscriber, $faker) {
    $subscriber->subscribers()->saveMany(factory(\Sendportal\Base\Models\Subscriber::class, 2)->make());
});
