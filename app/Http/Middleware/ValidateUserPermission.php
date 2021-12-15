<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class ValidateUserPermission
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
        
            //Comprobar los permisos
            if($req->user->job =='Direccion' || $req->user->job =='RRHH'){
                return $next($req);
            }else{
                $respuesta['msg'] = "No cuenta con permisos para ejecutar esta funcion";   
            }
            return response()->json($respuesta);

            /*if(Hash::check($req->password, $user->password)){
                //Los datos ingresados existen y son validos
                //Generamos el api_token
                do{
                    $token = Hash::make($user->id.now());    
                }while(User::where('api_token', $token)->first());

                $user->api_token =$token;
                $user->save();
                $respuesta['msg'] = "El token del usuario con email ".$user->email. " es ".$user->api_token;

            }else{
                //El usuario existe pero la contraseña es incorrecta
                $respuesta['msg'] = "Contraseña incorrecta, intentelo nuevamente";
            }*/
            
        
        
    }
}
