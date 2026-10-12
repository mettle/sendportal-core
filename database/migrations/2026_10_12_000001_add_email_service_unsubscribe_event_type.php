<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Sendportal\Base\Models\UnsubscribeEventType;

class AddEmailServiceUnsubscribeEventType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('sendportal_unsubscribe_event_types')->insert([
            'id' => UnsubscribeEventType::EMAIL_SERVICE,
            'name' => 'Email Service',
        ]);
    }
}
