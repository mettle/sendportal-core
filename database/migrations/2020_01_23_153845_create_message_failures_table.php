<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageFailuresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sendportal_message_failures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('message_id');
            $table->string('severity')->nullable()->default(null);
            $table->mediumText('description')->nullable()->default(null);
            $table->timestamp('failed_at')->nullable()->default(null);
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('sendportal_messages');
        });
    }
}
