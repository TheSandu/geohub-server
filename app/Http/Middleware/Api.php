<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class Api
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $user = User::where('remember_token', $request->header('token'))->first();

        if($request->hasHeader('token')) {
            if($user == null)
                return response()->json(['error'=>'Unauthorised'], 401);

            return $next($request);
        }

        return response()->json(['error'=>'Unauthorised'], 401);
    }

}
