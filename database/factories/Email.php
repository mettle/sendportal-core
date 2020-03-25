<?php

use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Email;
use Faker\Generator as Faker;

$factory->define(Email::class, function (Faker $faker)
{
    return [
        'mailable_id' => function ()
        {
            return factory(Campaign::class)->create()->id;
        },
        'mailable_type' => Campaign::class,
        'subject' => $faker->sentence,
        'content' => $faker->paragraph,
        'from_name' => $faker->firstName . ' ' . $faker->lastName,
        'from_email' => $faker->safeEmail,
    ];
});
