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
        Schema::create('ingredient_recipe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->nullable()->constrained('recipe')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredient')->cascadeOnDelete();
            $table->foreignId('ingredient_section_id')->nullable()->constrained('ingredient_section')->cascadeOnDelete();
            $table->decimal('amount');
            $table->string('measurement_unit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ingredient_recipe');
    }
};
