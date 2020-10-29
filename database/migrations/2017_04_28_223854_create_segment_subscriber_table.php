<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSegmentSubscriberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sendportal_segment_subscriber', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('segment_id');
            $table->unsignedInteger('subscriber_id');
            $table->timestamps();

            $table->foreign('segment_id')->references('id')->on('sendportal_segments');
            $table->foreign('subscriber_id')->references('id')->on('sendportal_subscribers');
        });
    }
}
