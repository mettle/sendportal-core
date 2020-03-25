<?php

use Sendportal\Base\Models\Provider;
use Sendportal\Base\Models\ProviderType;
use Sendportal\Base\Models\Team;
use Faker\Generator as Faker;

$factory->define(Provider::class, function (Faker $faker)
{
    return [
        'name' => ucwords($faker->word),
        'team_id' => factory(Team::class),
        'type_id' => $faker->randomElement(ProviderType::pluck('id')),
        'settings' => ['foo' => 'bar'],
    ];
});
