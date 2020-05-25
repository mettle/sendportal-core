<?php

declare(strict_types=1);

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;

$factory->define(Segment::class, static function (Faker $faker) {
    return [
        'name' => ucwords($faker->unique()->word),
    ];
});

$factory->afterCreatingState(Segment::class, 'subscribed', static function (Segment $segment) {
    $segment->subscribers()->saveMany(factory(Subscriber::class, 2)->make());
});
