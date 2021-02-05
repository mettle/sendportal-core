<?php

use Illuminate\Support\Facades\DB;
use Sendportal\Base\UpgradeMigration;

class AddCancelledCampaignStatus extends UpgradeMigration
{
    public function up()
    {
        $prefix = $this->getPrefix();

        DB::table("{$prefix}campaign_statuses")
            ->insert([
                'id' => 5,
                'name' => 'Cancelled',
            ]);
    }
}
