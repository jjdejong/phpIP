<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('default_actor', function (Blueprint $table) {
            if (! Schema::hasColumn('default_actor', 'updated_at')) {
                $table->timestamps();
            }
        });
    }

    public function down()
    {
        Schema::table('default_actor', function (Blueprint $table) {
            if (Schema::hasColumn('default_actor', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
            if (Schema::hasColumn('default_actor', 'created_at')) {
                $table->dropColumn('created_at');
            }
        });
    }
};
