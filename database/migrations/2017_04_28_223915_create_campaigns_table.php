<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sendportal_campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('workspace_id')->index();
            $table->string('name');
            $table->unsignedInteger('status_id')->default(1);
            $table->unsignedInteger('template_id')->nullable();
            $table->unsignedInteger('email_service_id')->nullable();
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->boolean('is_open_tracking')->default(true);
            $table->boolean('is_click_tracking')->default(true);
            $table->mediumInteger('sent_count')->nullable()->default(0);
            $table->mediumInteger('open_count')->nullable()->default(0);
            $table->mediumInteger('click_count')->nullable()->default(0);
            $table->boolean('send_to_all')->default(false);
            $table->boolean('save_as_draft')->default(true);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();

            $table->foreign('status_id')->references('id')->on('sendportal_campaign_statuses');
            $table->foreign('template_id')->references('id')->on('sendportal_templates');
            $table->foreign('email_service_id')->references('id')->on('sendportal_email_services');
        });
    }
}
