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
        /**
         *  Semester can be 0 = Wintersemester or 1 = Sommersemester
         * 
         * */
        Schema::create('htw_dresden_pillnitz_course', function (Blueprint $table) {
            $table->integer('identifier')->unsigned();
            $table->string('title');
            $table->boolean('semester');

            $table->primary('identifier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('htw_dresden_pillnitz_course');
    }
};
