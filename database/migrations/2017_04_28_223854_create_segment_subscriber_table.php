<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Sendportal\Base\UpgradeMigration;

class CreateSegmentSubscriberTable extends UpgradeMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = $this->getPrefix();

        Schema::create("{$prefix}segment_subscriber", function (Blueprint $table) use ($prefix) {
            $table->increments('id');
            $table->unsignedInteger('segment_id');
            $table->unsignedInteger('subscriber_id');
            $table->timestamps();

            $table->foreign('segment_id')->references('id')->on("{$prefix}segments");
            $table->foreign('subscriber_id')->references('id')->on("{$prefix}subscribers");
        });
    }
}
