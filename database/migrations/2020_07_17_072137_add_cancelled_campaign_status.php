<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCancelledCampaignStatus extends Migration
{
    public function up()
    {
        DB::table('sendportal_campaign_statuses')
            ->insert([
                'id' => 5,
                'name' => 'Cancelled',
            ]);
    }
}
