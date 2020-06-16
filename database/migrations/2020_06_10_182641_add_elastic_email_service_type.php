<?php

use Illuminate\Database\Migrations\Migration;
use Sendportal\Base\Models\EmailServiceType;

class AddElasticEmailServiceType extends Migration
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
            'id'   => EmailServiceType::ELASTIC,
            'name' => 'ElasticEmail',
        ]);
    }
}
