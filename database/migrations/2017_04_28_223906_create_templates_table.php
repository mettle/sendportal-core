<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('workspace_id');
            $table->string('name');
            $table->text('content')->nullable();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces');
        });
    }
}
