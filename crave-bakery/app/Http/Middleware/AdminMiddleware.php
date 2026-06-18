<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = $request->user();

        // if is not logged in 
        if (!$user) {
            return redirect()->route('login');
        }

        // if not admin or super admin
        if (! $user->isAdmin() && ! $user->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        return $next($request);
    }
}
