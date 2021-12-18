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

Route::prefix('users')->group(function(){

    Route::post('/recoverPassword',[UsersController::class,'recoverPassword']);
    
});


Route::post('/login',[UsersController::class,'login']);


Route::middleware(['apitoken','permission'])->prefix('users')->group(function(){

    Route::put('/registerUser',[UsersController::class,'registerUser']);
    Route::put('/listEmployees',[UsersController::class,'listEmployees']);
    Route::post('/employeeDetail',[UsersController::class,'employeeDetail']);
    Route::post('/profile',[UsersController::class,'profile'])->withoutMiddleware('permission');
    Route::put('/editProfile',[UsersController::class,'editProfile']);
        
});
