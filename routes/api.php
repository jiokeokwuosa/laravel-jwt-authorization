<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


//api-header middleware is needed to append a 
//Access-Control-Allow-Origin header to all responses from the server
Route::group(['middleware' => ['jwt-auth','api-header']], function () {
  
    // all routes to protected resources are registered here  
    Route::get('users/list', function(){
        $users = App\User::all();
        
        $response = ['success'=>true, 'data'=>$users];
        return response()->json($response, 201);
    });

    Route::get('user/thisuser','UsersController@getAuthenticatedUser');
    
});

Route::group(['middleware' => 'api-header'], function () {
  
    // The registration and login requests doesn't come with tokens 
    // as users at that point have not been authenticated yet
    // Therefore the jwtMiddleware will be exclusive of them

    Route::post('user/login', 'UsersController@login');
    Route::post('user/register', 'UsersController@register');
});

Route::get('me', 'UsersController@index');