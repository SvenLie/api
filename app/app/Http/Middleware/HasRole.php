<?php

namespace App\Http\Middleware;

use App\Models\UserRole;
use Closure;
use Illuminate\Support\Facades\Auth;

abstract class HasRole
{
    protected string $roleIdentifier = '';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $hasRole = false;

        if (Auth::user()) {
            $user = Auth::user();
            /** @var UserRole[] $roles */
            foreach ($user->roles as $role) {
                if ($role->identifier == $this->roleIdentifier) {
                    $hasRole = true;
                    break;
                }
            }
        }

        if (!$hasRole) return response()->json(['message' => 'Unauthorized'], 401);

        return $next($request);
    }

    public function setRoleIdentifier(string $roleIdentifier)
    {
        $this->roleIdentifier = $roleIdentifier;
    }
}
