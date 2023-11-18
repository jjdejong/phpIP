<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        Schema::table('event', function (Blueprint $table) {
            $table->foreign('alt_matter_id')->references('id')->on('matter')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('matter_id')->references('id')->on('matter')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('code')->references('code')->on('event_name')->onUpdate('CASCADE')->onDelete('RESTRICT');
        });
    }

    public function down()
    {
        Schema::table('event', function (Blueprint $table) {
            $table->dropForeign(['alt_matter_id']);
            $table->dropForeign(['matter_id']);
            $table->dropForeign(['code']);
        });
    }
};
