<?php

use Illuminate\Support\Facades\DB;
use Sendportal\Base\UpgradeMigration;

class AddCancelledCampaignStatus extends UpgradeMigration
{
    public function up()
    {
        $campaign_statuses = $this->getTableName('campaign_statuses');

        DB::table($campaign_statuses)
            ->insert([
                'id' => 5,
                'name' => 'Cancelled',
            ]);
    }
}
