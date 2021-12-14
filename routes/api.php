<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::prefix('users')->group(function(){

//     Route::put('/registerUser',[UsersController::class,'registerUser']);
//     Route::post('/desactivar/{id}',[UsuariosController::class,'desactivar']);
//     Route::post('/editar/{id}',[UsuariosController::class,'editar']);
//     Route::get('/listar',[UsuariosController::class,'listar']);
//     Route::get('/ver/{id}',[UsuariosController::class,'ver']);
//     Route::put('/comprar_curso/{usuario_id}/{curso_id}',[UsuariosController::class,'comprar_curso']);
//     Route::get('/listar_comprados/{usuario_id}',[UsuariosController::class,'listar_comprados']);
//     Route::post('/login',[UsersController::class,'login']);
// });


Route::middleware(['permission', 'apitoken'])->prefix('users')->group(function(){

    Route::post('/login',[UsersController::class,'login'])->withoutMiddleware('apitoken');
    Route::put('/registerUser',[UsersController::class,'registerUser']);
    


});


//Route::middleware('apitoken')->get('/protegido',....)