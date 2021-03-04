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
            $foreignKeys = $this->listTableForeignKeys('sendportal_segment_subscriber');

            if (in_array('sendportal_segment_subscriber_segment_id_foreign', $foreignKeys)) {
                $table->dropForeign('sendportal_segment_subscriber_segment_id_foreign');
            } elseif (in_array('segment_subscriber_segment_id_foreign', $foreignKeys)) {
                $table->dropForeign('segment_subscriber_segment_id_foreign');
            }

            $table->renameColumn('segment_id', 'tag_id');

            $table->foreign('tag_id')->references('id')->on('sendportal_tags');
        });

        Schema::rename("sendportal_segment_subscriber", "sendportal_tag_subscriber");


        Schema::table('sendportal_campaign_segment', function (Blueprint $table) {
            $foreignKeys = $this->listTableForeignKeys('sendportal_campaign_segment');

            if (in_array('sendportal_campaign_segment_segment_id_foreign', $foreignKeys)) {
                $table->dropForeign('sendportal_campaign_segment_segment_id_foreign');
            } elseif (in_array('campaign_segment_segment_id_foreign', $foreignKeys)) {
                $table->dropForeign('campaign_segment_segment_id_foreign');
            }

            $table->renameColumn('segment_id', 'tag_id');

            $table->foreign('tag_id')->references('id')->on('sendportal_tags');
        });

        Schema::rename("sendportal_campaign_segment", "sendportal_campaign_tag");
    }

    protected function listTableForeignKeys(string $table): array
    {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();

        return array_map(function ($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));
    }
}
