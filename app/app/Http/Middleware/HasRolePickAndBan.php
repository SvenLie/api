<?php

namespace App\Http\Middleware;

use App\Models\UserRole;
use Closure;
use Illuminate\Support\Facades\Auth;

class HasRolePickAndBan
{
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
                if ($role->identifier == 'pick-and-ban') {
                    $hasRole = true;
                    break;
                }
            }
        }

        if (!$hasRole) return response()->json(['message' => 'Unauthorized'], 401);

        return $next($request);
    }
}
