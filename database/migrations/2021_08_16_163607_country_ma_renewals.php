<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        DB::table('country')->where('iso', 'MA')->update([
            'renewal_base' => 'FIL',
            'checked_on' => '2021-08-16',
        ]);
    }

    public function down()
    {
        //
    }
};
