<?php

declare(strict_types=1);

namespace Database\Factories;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;

//$factory->define(Subscriber::class, static function (Faker $faker) {
//    return [
//        'workspace_id' => \Sendportal\Base\Facades\Sendportal::currentWorkspaceId(),
//        'hash' => $faker->uuid,
//        'first_name' => $faker->firstName,
//        'last_name' => $faker->lastName,
//        'email' => $faker->safeEmail
//    ];
//});
//
//
//$factory->afterCreatingState(Subscriber::class, 'segmented', static function (Subscriber $subscriber) {
//    $subscriber->segments()->saveMany(factory(Segment::class, 2)->make());
//});

class SubscriberFactory extends Factory
{
    /** @var string */
    protected $model = Subscriber::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'hash' => $this->faker->uuid,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->safeEmail
        ];
    }
}
