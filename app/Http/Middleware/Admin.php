<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()->usertype == 'admin') {
            return $next($request);
        }
        // Return the user to the homepage if they aren't an admin
        return redirect('/');

        // Alternatively, you can use abort(401); to show an unauthorized error page.
    }
}
