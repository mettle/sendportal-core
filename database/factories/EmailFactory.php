<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Email;

$factory->define(Email::class, function (Faker $faker)
{
    return [
        'mailable_id' => factory(Campaign::class),
        'mailable_type' => Campaign::class,
        'subject' => $faker->sentence,
        'content' => $faker->paragraph,
        'from_name' => $faker->firstName . ' ' . $faker->lastName,
        'from_email' => $faker->safeEmail,
    ];
});
