<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\Workspace;

$factory->define(Subscriber::class, static function (Faker $faker) {
    return [
        'workspace_id' => \Sendportal\Base\Facades\Sendportal::currentWorkspaceId(),
        'hash' => $faker->uuid,
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->safeEmail
    ];
});


$factory->afterCreatingState(Subscriber::class, 'segmented', static function (Subscriber $subscriber) {
    $subscriber->segments()->saveMany(factory(Segment::class, 2)->make());
});
