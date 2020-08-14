<?php

use Illuminate\Database\Migrations\Migration;
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
        EmailServiceType::unguard();

        EmailServiceType::create([
            'id' => EmailServiceType::MAILJET,
            'name' => 'Mailjet'
        ]);
    }
}
