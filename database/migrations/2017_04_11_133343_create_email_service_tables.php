<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Sendportal\Base\Models\EmailServiceType;

class CreateEmailServiceTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('sendportal_email_service_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $this->seedEmailServiceTypes();

        \Schema::create('sendportal_email_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('workspace_id')->index();
            $table->string('name')->nullable();
            $table->unsignedInteger('type_id');
            $table->mediumText('settings');
            $table->timestamps();

            $table->foreign('type_id')->references('id')->on('sendportal_email_service_types');
        });
    }

    protected function seedEmailServiceTypes()
    {
        EmailServiceType::unguard();

        EmailServiceType::create([
            'id' => EmailServiceType::SES,
            'name' => 'SES'
        ]);

        EmailServiceType::create([
            'id' => EmailServiceType::SENDGRID,
            'name' => 'SendGrid'
        ]);

        EmailServiceType::create([
            'id' => EmailServiceType::MAILGUN,
            'name' => 'Mailgun'
        ]);

        EmailServiceType::create([
            'id' => EmailServiceType::POSTMARK,
            'name' => 'Postmark'
        ]);
    }
}
