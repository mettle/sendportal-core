<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Sendportal\Base\UpgradeMigration;

class CreateCampaignStatusesTable extends UpgradeMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sendportal_campaign_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        DB::table('sendportal_campaign_statuses')
            ->insert([
               ['name' => 'Draft'],
               ['name' => 'Queued'],
               ['name' => 'Sending'],
               ['name' => 'Sent'],
            ]);
    }
}
