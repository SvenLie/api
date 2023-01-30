<?php

namespace App\Http\Middleware;

class HasRoleRecipes extends HasRole
{
    public function __construct() {
        $this->setRoleIdentifier('recipes');
    }
}
