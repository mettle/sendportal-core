<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Sendportal\Base\Models\UnsubscribeEventType;

class CreateUnsubscribedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unsubscribe_event_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        $types = [
            UnsubscribeEventType::BOUNCE => 'Bounce',
            UnsubscribeEventType::COMPLAINT => 'Complaint',
            UnsubscribeEventType::MANUAL_BY_ADMIN => 'Manual by Admin',
            UnsubscribeEventType::MANUAL_BY_SUBSCRIBER => 'Manual by Subscriber',
        ];

        foreach ($types as $id => $name) {
            DB::table('unsubscribe_event_types')->insert([
                'id' => $id,
                'name' => $name
            ]);
        }
    }
}
