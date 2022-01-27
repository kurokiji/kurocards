<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PrivateAndProValidator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $profile = $request->loggedUser->profile;
        if ($profile == "professional" || $profile == "private") {
            return $next($request);
        } else {
            return response()->json(['status' => 0, 'msg' => "You don't have permission to perform this operation"]);
        }
    }
}
