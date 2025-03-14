<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect('login');
        }

        // Check if user is an admin (assuming you have a role or is_admin field)
        // Modify this condition based on how you identify admins in your app
        if (Auth::user()->role !== 'admin') {
            // Redirect non-admin users to home or show an error
            return redirect()->route('home')->with('error', 'Access denied. You need admin privileges.');
        }

        return $next($request);
    }
}
