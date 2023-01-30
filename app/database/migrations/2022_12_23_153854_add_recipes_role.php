<?php

use Illuminate\Database\Migrations\Migration;

class AddRecipesRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::table('user_role')->insert(
            array(
                'identifier' => 'recipes',
                'name' => 'Rezepte',
                'isSelfAssignable' => true
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
