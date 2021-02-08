<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Sendportal\Base\UpgradeMigration;

class DropSegmentNameUnique extends UpgradeMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $segments = $this->getTableName('segments');

        Schema::table($segments, function (Blueprint $table) use ($segments) {
            $table->dropUnique("{$segments}_name_unique");
        });
    }
}
