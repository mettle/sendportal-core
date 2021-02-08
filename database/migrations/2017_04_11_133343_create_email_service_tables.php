<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Sendportal\Base\Models\EmailServiceType;
use Sendportal\Base\UpgradeMigration;

class CreateEmailServiceTables extends UpgradeMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sendportal_email_service_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $this->seedEmailServiceTypes();

        Schema::create('sendportal_email_services', function (Blueprint $table) {
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
        $serviceTypes = [
            [
                'id' => EmailServiceType::SES,
                'name' => 'SES'
            ],
            [
                'id' => EmailServiceType::SENDGRID,
                'name' => 'SendGrid'
            ],
            [
                'id' => EmailServiceType::MAILGUN,
                'name' => 'Mailgun'
            ],
            [
                'id' => EmailServiceType::POSTMARK,
                'name' => 'Postmark'
            ]
        ];

        foreach ($serviceTypes as $type) {
            DB::table('sendportal_email_service_types')
                ->insert(
                    $type + [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
        }
    }
}
