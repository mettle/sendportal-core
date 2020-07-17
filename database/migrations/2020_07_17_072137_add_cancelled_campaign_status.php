<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCancelledCampaignStatus extends Migration
{
    public function up()
    {
        DB::table('campaign_statuses')
            ->insert([
                'id' => 5,
                'name' => 'Cancelled',
            ]);
    }
}
