<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkspaceUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workspace_users', function (Blueprint $table) {
            $table->unsignedInteger('workspace_id')->index();
            $table->unsignedInteger('user_id')->index();
            $table->string('role', 20);
            $table->timestamps();

            $table->unique(['workspace_id', 'user_id']);
        });
    }
}
