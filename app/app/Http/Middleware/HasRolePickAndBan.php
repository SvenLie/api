<?php

namespace App\Http\Middleware;

class HasRolePickAndBan extends HasRole
{
    public function __construct() {
        $this->setRoleIdentifier('pick-and-ban');
    }
}
