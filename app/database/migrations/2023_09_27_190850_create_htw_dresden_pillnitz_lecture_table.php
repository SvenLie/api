<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('htw_dresden_pillnitz_lecture', function (Blueprint $table) {
            $table->id();
            
            $table->integer('module_id')->unsigned();
            $table->string('title');
            $table->string('start');
            $table->string('end');
            $table->string('lecturer');
            $table->string('type');
            $table->string('groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('htw_dresden_pillnitz_lecture');
    }
};
