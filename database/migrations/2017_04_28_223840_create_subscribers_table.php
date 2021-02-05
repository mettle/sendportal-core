<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Sendportal\Base\UpgradeMigration;

class CreateSubscribersTable extends UpgradeMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = $this->getPrefix();

        Schema::create("{$prefix}subscribers", function (Blueprint $table) use ($prefix) {
            $table->increments('id');
            $table->unsignedInteger('workspace_id')->index();
            $table->uuid('hash')->unique();
            $table->string('email')->index();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamp('unsubscribed_at')->nullable()->index();
            $table->unsignedInteger('unsubscribe_event_id')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->foreign('unsubscribe_event_id')->references('id')->on("{$prefix}unsubscribe_event_types");
        });
    }
}
