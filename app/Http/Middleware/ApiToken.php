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
    public function handle(Request $req, Closure $next)
    {
        //Buscar al usuario
        $apitoken = $req->api_token;
        //print($apitoken);

        $user = User::where('api_token',$apitoken)->first();

        if(!$user){
            //Erro
            die("token mal");
        }else{
            $req->user = $user;
            return $next($req);
        }
            
    }
}
