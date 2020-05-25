<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Sendportal\Base\Models\EmailServiceType;

class AddSesEmailServiceType extends Migration
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
            'id' => EmailServiceType::SES,
            'name' => 'SES'
        ]);
    }
}
