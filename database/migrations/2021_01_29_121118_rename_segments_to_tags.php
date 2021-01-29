<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameSegmentsToTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('sendportal_segments', 'sendportal_tags');

        Schema::table('sendportal_segment_subscriber', function (Blueprint $table) {
            $table->dropForeign(['segment_id']);

            $table->renameColumn('segment_id', 'tag_id');

            $table->foreign('tag_id')->references('id')->on('sendportal_tags');
        });

        Schema::rename("sendportal_segment_subscriber", "sendportal_tag_subscriber");


        Schema::table('sendportal_campaign_segment', function (Blueprint $table) {
            $table->dropForeign(['segment_id']);

            $table->renameColumn('segment_id', 'tag_id');

            $table->foreign('tag_id')->references('id')->on('sendportal_tags');
        });

        Schema::rename("sendportal_campaign_segment", "sendportal_campaign_tag");
    }
}
