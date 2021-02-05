<?php

use Illuminate\Support\Facades\DB;
use Sendportal\Base\Models\EmailServiceType;
use Sendportal\Base\UpgradeMigration;

class AddMailjetEmailService extends UpgradeMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = $this->getPrefix();

        DB::table("{$prefix}email_service_types")
            ->insert(
                [
                    'id' => EmailServiceType::MAILJET,
                    'name' => 'Mailjet',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
    }
}
