<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Hash;


class UsersController extends Controller
{
    //
    public function registerUser(Request $req){

        $validator = Validator::make(json_decode($req->getContent(),true),
        [

            "name"=>["required","max:50"],
            "email"=>["required","email","unique:App\Models\User,email","max:30"],
            "password"=>["required","regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/"],
            "job"=>["required",Rule::in(['Direccion', 'RRHH', 'Empleado'])],
            "salary"=>["required"],
            "biography"=>["required"]

            // 'name'=>'required|max:50',
            // 'email'=>'required|email|unique:App\Models\User,email|max:30',
            // 'password'=>'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/',
            // 'job'=>'required| Rule::in(["Direccion", "RRHH", "Empleado"])',
            // 'salary'=>'required',
            // 'biography'=>'required'

        ]);

        if ($validator->fails()){
          
            //respuestas de error
            // return response()->json();
            // throw new \Exception('Los datos ingresados no cumplen con los parametros de registro');
            // return redirect('post/create')
            //             ->withErrors($validator)
            //             ->withInput();
            // $respuesta['msg'] = "Los datos ingresados no cumplen los parametros, verifiquelos e intente nuevamente";
            // return response()->json($respuesta);
        } else{
            //Generar el nuevo usuario
            $respuesta = ["status" => 1, "msg" => ""];

            $data = $req->getContent();
            $data = json_decode($data);
            $user = new User();

            $user->name = $data->name;
            $user->email = $data->email;
            $user->password = Hash::make($data->password);
            $user->job = $data->job;
            $user->salary = $data->salary;
            $user->biography = $data->biography;

            try{
                $user->save();
                $respuesta['msg'] = "Persona guardada con id ".$user->id;
                               
            }catch(\Exception $e){
                $respuesta['status'] = 0;
                $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
            }
    
            return response()->json($respuesta);


        }

    }


    public function login(Request $req){

        $data = $req->getContent();
        $data = json_decode($data);

        //Buscar el email
        $email = $data->email;

        //Validacion
        
        try{
            if(User::where('email', '=', $data->email)->first()){

                $user = User::where('email',$email)->first();

                if(Hash::check($req->password, $user->password)){
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
                }
                
            }else{
                
                $respuesta['msg'] = "Usuario no registrado";
            }
            
        }catch(\Exception $e){
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($respuesta);

    }

}
