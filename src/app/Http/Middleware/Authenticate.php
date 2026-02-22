<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            if ($request->is('admin/*') || $request->is('stamp_correction_request/approve/*')) {
                return route('admin.login.create');
            }
            return route('login');
        }
    }
}
