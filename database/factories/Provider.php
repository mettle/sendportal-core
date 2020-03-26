<?php

use Sendportal\Base\Models\Provider;
use Sendportal\Base\Models\ProviderType;
use Sendportal\Base\Models\Workspace;
use Faker\Generator as Faker;

$factory->define(Provider::class, function (Faker $faker)
{
    return [
        'name' => ucwords($faker->word),
        'workspace_id' => factory(Workspace::class),
        'type_id' => $faker->randomElement(ProviderType::pluck('id')),
        'settings' => ['foo' => 'bar'],
    ];
});
