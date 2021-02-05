<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Sendportal\Base\UpgradeMigration;

class CreateMessageFailuresTable extends UpgradeMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $prefix = $this->getPrefix();

        Schema::create("{$prefix}message_failures", function (Blueprint $table) use ($prefix) {
            $table->bigIncrements('id');
            $table->unsignedInteger('message_id');
            $table->string('severity')->nullable()->default(null);
            $table->mediumText('description')->nullable()->default(null);
            $table->timestamp('failed_at')->nullable()->default(null);
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on("{$prefix}messages");
        });
    }
}
