<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('workspace_id');
            $table->string('name');
            $table->unsignedInteger('status_id')->default(1);
            $table->unsignedInteger('template_id')->nullable();
            $table->unsignedInteger('provider_id')->nullable();
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->mediumInteger('sent_count')->nullable()->default(0);
            $table->mediumInteger('open_count')->nullable()->default(0);
            $table->mediumInteger('click_count')->nullable()->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
        });

        Schema::table('campaigns', function(Blueprint $table) {
            $table->foreign('workspace_id')->references('id')->on('workspaces');
            $table->foreign('status_id')->references('id')->on('campaign_statuses');
            $table->foreign('template_id')->references('id')->on('templates');
            $table->foreign('provider_id')->references('id')->on('providers');
        });
    }
}
