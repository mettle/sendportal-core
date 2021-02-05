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
        $prefix = $this->getPrefix();

        Schema::table("{$prefix}segments", function (Blueprint $table) use ($prefix) {
            $table->dropUnique("{$prefix}segments_name_unique");
        });
    }
}
