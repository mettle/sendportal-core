<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Sendportal\Base\UpgradeMigration;

class CreateMessageUrlsTable extends UpgradeMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sendportal_message_urls', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source_type')->index();
            $table->unsignedInteger('source_id')->index();
            $table->string('hash')->index();
            $table->string('url')->index();
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamps();
        });
    }
}
