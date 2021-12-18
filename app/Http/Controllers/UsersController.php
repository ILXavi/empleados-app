<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\Password;


class UsersController extends Controller
{
    //
    public function registerUser(Request $req){

        $validator = Validator::make(json_decode($req->getContent(),true),
        [

            "name"=>["required","max:50"],
            "email"=>["required","email","unique:App\Models\User,email","max:50"],
            "password"=>["required","regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/"],
            "job"=>["required",Rule::in(['Direccion', 'RRHH', 'Empleado'])],
            "salary"=>["required"],
            "biography"=>["required"]

        ]);

        if ($validator->fails()){
          
            echo "Errors: <br>";
            $errors = $validator->errors();
            echo $errors->first('name');
            echo $errors->first('email');
            echo $errors->first('password');            
            echo $errors->first('job');
            echo $errors->first('salary');
            echo $errors->first('biography');
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

                if(Hash::check($data->password, $user->password)){
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

    public function recoverPassword(Request $req){

        //Obtenemos el email
        $data = $req->getContent();
        $data = json_decode($data);

        //Buscar el email
        $email = $data->email;

        //Validacion
        
        try{
            if(User::where('email', '=', $data->email)->first()){

                $user = User::where('email',$email)->first();

                $user->api_token = null;
                
                //Generamos nueva contraseña aleatoria
                $characters = "0123456789aAbBcCdDeEfFgFhH";
                $characterLength = strlen($characters);
                $newPassword = '';
                for ($i=0; $i < 6; $i++) { 
                    $newPassword .= $characters[rand(0, $characterLength - 1)];
                } 

                //Le agregamos la nueva contraseña al usuario
                $user->password = Hash::make($newPassword);
                $user->save();

                //La enviamos por email
                
                Mail::to($user->email)->send(new Password($newPassword));  
                $respuesta['msg'] = "Se ha enviado la nueva contraseña a su correo";
                //$respuesta['msg'] = "La nueva contraseña es ".$newPassword;
                
            }else{
                
                $respuesta['msg'] = "Usuario no registrado";
            }
            
        }catch(\Exception $e){
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($respuesta);

    }


    function listEmployees(Request $req){

        $data = $req->getContent();
        $data = json_decode($data);

        //Buscar el email
        $apitoken = $data->api_token;
    
        //Validacion
        
        try{
            if(User::where('api_token', '=', $data->api_token)->first()){

                $user = User::where('api_token',$apitoken)->first();
                
                //verificamos el cargo del solicitante
                if($user->job == 'Direccion'){

                    $users = DB::table('users')
                    ->select(['name','job','salary'])
                    ->where('users.job' ,'like', "RRHH")
                    ->orwhere('users.job' ,'like', "Empleado")
                    ->get();

                }else{
                    $users = DB::table('users')
                    ->select(['name','job','salary'])
                    ->where('users.job' ,'like', "Empleado")
                    ->get();
                }

                $respuesta['msg'] = $users;
                
            }else{
                
                $respuesta['msg'] = "Token invalido";
            }
            
        }catch(\Exception $e){
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($respuesta);

    }

    function employeeDetail(Request $req){

        $data = $req->getContent();
        $data = json_decode($data);

        //Buscar el email
        $apitoken = $data->api_token;
        $requestedUserId = $data->requestedUserId;
    
        //Validacion
        
        try{
            if(User::where('api_token', '=', $data->api_token)->first()){

                $user = User::where('api_token',$apitoken)->first();

                if(User::where('id', '=', $data->requestedUserId)->first()){

                    $userRequested = User::where('id',$requestedUserId)->first();

                    if($user->job == 'Direccion'){

                        $users = DB::table('users')
                        ->select(['name','email','job','salary','biography'])
                        ->where('id' ,'=', $requestedUserId)
                        ->where(function ($query) {
                            $query->where('users.job' ,'like', "RRHH")
                                  ->orWhere('users.job' ,'like', "Empleado");
                        })
                        ->get();
  
                    }else{

                        $users = DB::table('users')
                        ->select(['name','email','job','salary','biography'])
                        ->where('id' ,'=', $requestedUserId)
                        ->where('users.job' ,'like', "Empleado")
                        ->get();
                    }

                    if($users->isEmpty()){
                        $respuesta['msg'] = "No tiene permisos para ver al usuario solicitado";
                    }else{
                        $respuesta['msg'] = $users;
                    }
                                       
                }else{
                    $respuesta['msg'] = "El id ingresado no se encuentra registrado";
                }
                
            }else{
                $respuesta['msg'] = "Token invalido";
            }
            
        }catch(\Exception $e){
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($respuesta);

    }

    function profile(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];

        $data = $req->getContent();
        $data = json_decode($data);

        //Buscar el email
        $apitoken = $data->api_token;
            
        //Validacion
        
        try{
            if(User::where('api_token', '=', $data->api_token)->first()){

                $user = User::where('api_token',$apitoken)->first();
                //$respuesta['msg'] = $user;

                $profile = DB::table('users')
                ->select(['id','name','email','job','salary','biography','created_at'])
                ->where('id' ,'=', $user->id)
                ->get();
               
                $respuesta['msg'] = $profile;
                              
            }else{
                
                $respuesta['msg'] = "Token invalido";
            }
            
        }catch(\Exception $e){
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($respuesta);

    }

    
    function editProfile(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];

        $data = $req->getContent();
        $data = json_decode($data);

        //Buscar el email
        $apitoken = $data->api_token;
        $requestedId = $data->id;
        //Validacion
        
        try{
            if(User::where('api_token', '=', $data->api_token)->first()){
               
                $user = User::where('api_token',$apitoken)->first();

                if(User::where('id', '=', $data->id)->first()){

                    $requestedUserId = User::where('id',$requestedId)->first();

                    if(($user->job == 'Direccion'&& ($requestedUserId->job == 'RRHH' || $requestedUserId->job == 'Empleado' || $user->id == $requestedUserId->id))
                        || ($user->job == 'RRHH'&& ($requestedUserId->job == 'Empleado'))
                    ){

                        if(isset($data->email)){

                            if ($requestedUserId->email == $data->email){

                                $validator = Validator::make(json_decode($req->getContent(),true),
                                [
                                    "name"=>["max:50"],
                                    "email"=>["email","max:50"],                            
                                    "password"=>["regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/"],
                                    "job"=>[Rule::in(['Direccion', 'RRHH', 'Empleado'])],
                                    "salary"=>["integer"]
                                   
                                ]);

                            }else{

                                $validator = Validator::make(json_decode($req->getContent(),true),
                                [
                                    "name"=>["max:50"],
                                    "email"=>["email","unique:App\Models\User,email","max:50"],                            
                                    "password"=>["regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/"],
                                    "job"=>[Rule::in(['Direccion', 'RRHH', 'Empleado'])],
                                    "salary"=>["integer"]
                                ]);
                            }
                            
                        }else{

                            $validator = Validator::make(json_decode($req->getContent(),true),
                            [
                                "name"=>["max:50"],
                                "email"=>["email","unique:App\Models\User,email","max:50"],                            
                                "password"=>["regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/"],
                                "job"=>[Rule::in(['Direccion', 'RRHH', 'Empleado'])],
                                "salary"=>["integer"]
                            ]);

                        }
                       
                        if ($validator->fails()){
                        
                            echo "Errors: <br>";
                            $errors = $validator->errors();
                            echo $errors->first('name');
                            echo $errors->first('email');
                            echo $errors->first('password');            
                            echo $errors->first('job');
                            echo $errors->first('salary');
                            echo $errors->first('biography');
                        } else{

                            //Almacenar la nueva informacion del usuario
                            if (isset($data->name)) {$requestedUserId->name = $data->name;}
                            if (isset($data->email)){$requestedUserId->email = $data->email;}
                            if (isset($data->password)){$requestedUserId->password = Hash::make($data->password);}
                            if (isset($data->job)){$requestedUserId->job = $data->job;}
                            if (isset($data->salary)){$requestedUserId->salary = $data->salary;}
                            if (isset($data->biography)){$requestedUserId->biography = $data->biography;}

                            try{
                                $requestedUserId->save();
                                $respuesta['msg'] = "Se han actualizado los datos del usuario ".$requestedUserId->id;
                                            
                            }catch(\Exception $e){
                                $respuesta['status'] = 0;
                                $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
                            }
                    
                            return response()->json($respuesta);
                        }
                        $respuesta['msg'] = "Revise los parametros e intente nuevamente";

                    }else{
                        $respuesta['msg'] = "No tiene permisos para editar al usuario solicitado";
                    }

                }else{
                    $respuesta['msg'] = "El id ingresado no corresponde a ningun usuario registrado";
                }

            }else{
                
                $respuesta['msg'] = "Token invalido";
            }
            
        }catch(\Exception $e){
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }

        return response()->json($respuesta);
    }
}

