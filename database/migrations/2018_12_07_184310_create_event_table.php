<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up()
    {
        Schema::create('event', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->char('code', 5)->index('code')->comment('Link to event_names table');
            $table->unsignedInteger('matter_id');
            $table->date('event_date')->nullable()->index('date');
            $table->unsignedInteger('alt_matter_id')->nullable()->index('alt_matter')->comment('Essentially for priority claims. ID of prior patent this event refers to');
            $table->string('detail', 45)->nullable()->index('number')->comment('Numbers or short comments');
            $table->string('notes', 150)->nullable();
            $table->char('creator', 16)->nullable();
            $table->char('updater', 16)->nullable();
            $table->timestamps();
            $table->unique(['matter_id', 'code', 'event_date', 'alt_matter_id'], 'uqevent');
        });
    }

    public function down()
    {
        Schema::dropIfExists('event');
    }
};
