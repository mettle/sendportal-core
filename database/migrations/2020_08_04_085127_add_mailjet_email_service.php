<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Sendportal\Base\Models\EmailServiceType;

class AddMailjetEmailService extends Migration
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
                    'id' => EmailServiceType::MAILJET,
                    'name' => 'Mailjet'
                ]
            );
    }
}
