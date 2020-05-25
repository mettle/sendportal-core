<?php

/** @var Factory $factory */

use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Models\Template;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

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
    $template = factory(Template::class)->create();

    return [
        'template_id' => $template->id,
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
