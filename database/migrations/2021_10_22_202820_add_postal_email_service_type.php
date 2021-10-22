<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Sendportal\Base\Models\EmailServiceType;

class AddPostalEmailServiceType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('sendportal_email_service_types')
            ->insert(
                [
                    'id' => EmailServiceType::Postal,
                    'name' => 'Postal',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
    }
}
