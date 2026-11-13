<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && (bool) auth()->user()->must_change_password) {
            // Allow access to change password page and logout
            if (!$request->is('admin/change-password*') && !$request->is('admin/logout*')) {
                return redirect('/admin/change-password');
            }
        }

        return $next($request);
    }
}
