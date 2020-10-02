<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;

$factory->define(Segment::class, static function (Faker $faker) {
    return [
        'workspace_id' => \Sendportal\Base\Facades\Sendportal::currentWorkspaceId(),
        'name' => ucwords($faker->unique()->word)
    ];
});

$factory->afterCreatingState(Segment::class, 'subscribed', static function (Segment $segment) {
    $segment->subscribers()->saveMany(factory(Subscriber::class, 2)->make());
});
