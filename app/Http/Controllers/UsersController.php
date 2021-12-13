<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


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
            
            
            //echo $errors->first('name');
            //echo $errors->first('name','email','password','job','salary','biography');

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
            $user->password = $data->password;
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

}
