<?php

namespace App\Http\Middleware;

use App\Http\Libraries\ManaCms;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CmsAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $module, $task = 'page')
    {
        if (ManaCms::checkAccess($module, $task)) {
            return $next($request);
        }

        return redirect('/')->with('error', 'Access Denied!');
    }
}
