<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Sendportal\Base\UpgradeMigration;

class CreateCampaignSegmentTable extends UpgradeMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $segments = $this->getTableName('segments');
        $campaigns = $this->getTableName('campaigns');

        Schema::create('sendportal_campaign_segment', function (Blueprint $table) use ($campaigns, $segments) {
            $table->increments('id');
            $table->unsignedInteger('segment_id');
            $table->unsignedInteger('campaign_id');
            $table->timestamps();

            $table->foreign('segment_id')->references('id')->on($segments);
            $table->foreign('campaign_id')->references('id')->on($campaigns);
        });
    }
}
