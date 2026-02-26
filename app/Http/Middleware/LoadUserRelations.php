<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LoadUserRelations
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            auth()->user()?->loadMissing('santri');
        }

        return $next($request);
    }
}
