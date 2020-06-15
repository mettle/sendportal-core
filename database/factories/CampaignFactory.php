<?php

/** @var Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Template;
use Sendportal\Base\Models\Workspace;

$factory->define(Campaign::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'workspace_id' => factory(Workspace::class),
        'subject' => $faker->title,
        'from_name' => $faker->name,
        'from_email' => $faker->email,
        'email_service_id' => factory(EmailService::class),
        'is_open_tracking' => true,
        'is_click_tracking' => true,
    ];
});

$factory->state(Campaign::class, 'withContent', function (Faker $faker) {
    return [
        'content' => $faker->paragraph,
    ];
});

$factory->state(Campaign::class, 'withTemplate', function () {
    return [
        'template_id' => factory(Template::class),
    ];
});

$factory->state(Campaign::class, 'sent', function () {
    return [
        'status_id' => CampaignStatus::STATUS_SENT,
    ];
});

$factory->state(Campaign::class, 'draft', function () {
    return [
        'status_id' => CampaignStatus::STATUS_DRAFT,
    ];
});

$factory->state(Campaign::class, 'withoutOpenTracking', static function () {
    return ['is_open_tracking' => false];
});

$factory->state(Campaign::class, 'withoutClickTracking', static function () {
    return ['is_click_tracking' => false];
});
