<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class PrefixTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->getTables() as $table) {
            Schema::rename($table, "sendportal_{$table}");
        }
    }

    /**
     * @return array
     */
    protected function getTables()
    {
        return [
            'campaign_segment',
            'campaign_statuses',
            'campaigns',
            'email_service_types',
            'email_services',
            'message_failures',
            'message_urls',
            'messages',
            'segment_subscriber',
            'segments',
            'subscribers',
            'templates',
            'unsubscribe_event_types',
        ];
    }
}
