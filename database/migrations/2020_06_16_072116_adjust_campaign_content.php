<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Sendportal\Base\UpgradeMigration;

class AdjustCampaignContent extends UpgradeMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $campaigns = $this->getTableName('campaigns');

        Schema::table($campaigns, function (Blueprint $table) {
            $table->longText('content')->nullable()->change();
        });
    }
}
