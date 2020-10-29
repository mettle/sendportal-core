<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sendportal_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('hash')->unique();
            $table->unsignedInteger('workspace_id')->index();
            $table->unsignedInteger('subscriber_id')->index();
            $table->string('source_type')->index();
            $table->unsignedInteger('source_id')->index();
            $table->string('recipient_email');
            $table->string('subject');
            $table->string('from_name');
            $table->string('from_email');
            $table->string('message_id')->index()->nullable();
            $table->string('ip')->nullable();
            $table->unsignedInteger('open_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamp('queued_at')->nullable()->default(null)->index();
            $table->timestamp('sent_at')->nullable()->default(null)->index();
            $table->timestamp('delivered_at')->nullable()->default(null)->index();
            $table->timestamp('bounced_at')->nullable()->default(null)->index();
            $table->timestamp('unsubscribed_at')->nullable()->default(null)->index();
            $table->timestamp('complained_at')->nullable()->default(null)->index();
            $table->timestamp('opened_at')->nullable()->default(null)->index();
            $table->timestamp('clicked_at')->nullable()->default(null)->index();
            $table->timestamps();
        });
    }
}
