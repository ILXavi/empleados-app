<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateUserPermission
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
        //Comprobar los permisos
        if($request->user->job =='Direccion' || $request->user->job =='RRHH'){
            return $next($request);
        }else{
            $respuesta['msg'] = "No cuenta con permisos para realizar esta accion";   
        }
        return response()->json($respuesta);
    }
}
