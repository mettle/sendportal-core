<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Sendportal\Base\UpgradeMigration;

class AdjustTemplateContent extends UpgradeMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $templates = $this->getTableName('templates');

        Schema::table($templates, function (Blueprint $table) {
            $table->longText('content')->change();
        });
    }
}
