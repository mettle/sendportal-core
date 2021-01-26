<?php

declare(strict_types=1);

namespace Database\Factories;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Segment;
use Sendportal\Base\Models\Subscriber;

//$factory->define(Segment::class, static function (Faker $faker) {
//    return [
//        'workspace_id' => \Sendportal\Base\Facades\Sendportal::currentWorkspaceId(),
//        'name' => ucwords($faker->unique()->word)
//    ];
//});
//
//$factory->afterCreatingState(Segment::class, 'subscribed', static function (Segment $segment) {
//    $segment->subscribers()->saveMany(factory(Subscriber::class, 2)->make());
//});

class SegmentFactory extends Factory
{
    /** @var string */
    protected $model = Segment::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Sendportal::currentWorkspaceId(),
            'name' => ucwords($this->faker->unique()->word)
        ];
    }
}
