<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Sendportal\Base\Models\ProviderType;

class AddSesProviderType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ProviderType::unguard();

        ProviderType::create([
            'id' => ProviderType::SES,
            'name' => 'SES'
        ]);
    }
}
