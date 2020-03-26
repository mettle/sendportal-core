<?php

/** @var Factory $factory */

use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\Subscriber;
use Sendportal\Base\Models\Workspace;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Message::class, function (Faker $faker) {
    return [
        'hash' => $faker->uuid,
        'workspace_id' => factory(Workspace::class),
        'subscriber_id' => factory(Subscriber::class),
        'source_type' => Campaign::class,
        'source_id' => factory(Campaign::class),
        'recipient_email' => $faker->email,
        'subject' => $faker->sentence(3),
        'from_name' => $faker->name,
        'from_email' => 'testing@mettle.io',
        'message_id' => null,
        'ip' => $faker->ipv4,
        'open_count' => 0,
        'click_count' => 0,
        'queued_at' => null,
        'sent_at' => null,
        'delivered_at' => null,
        'bounced_at' => null,
        'unsubscribed_at' => null,
        'complained_at' => null,
        'opened_at' => null,
        'clicked_at' => null,
    ];
});
