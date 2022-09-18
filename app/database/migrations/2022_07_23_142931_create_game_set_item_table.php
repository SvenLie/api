<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameSetItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_set_item', function (Blueprint $table) {
            $table->id();
            $table->integer('game_set_id')->unsigned();
            $table->string('name');
            $table->timestamps();

            $table->foreign('game_set_id')->references('id')->on('game_set')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_set_item');
    }
}
