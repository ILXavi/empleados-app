<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class ApiToken
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
        //Buscar al usuario
        $apitoken = $req->api_token;

        $user = User::where('api_token',$apitoken)->first();

        if(!$user){
            //Error
        }else{
            $request->user = $user;
            return $next($request);
        }
            
    }
}
