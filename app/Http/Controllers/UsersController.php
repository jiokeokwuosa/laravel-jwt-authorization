<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use JWTAuth;
use JWTAuthException;

class UsersController extends Controller
{
    public function index()
    {
        return "we are here bro";
    }
    
    private function getToken($email, $password)
    {
        $token = null;       
        try {
            if (!$token = JWTAuth::attempt( ['email'=>$email, 'password'=>$password])) {
                return response()->json([
                    'response' => 'error',
                    'message' => 'Password or email is invalid',
                    'token'=>$token
                ]);
            }
        } catch (JWTAuthException $e) {
            return response()->json([
                'response' => 'error',
                'message' => 'Token creation failed',
            ]);
        }
        return $token;
    }

    public function login(Request $request)
    {
        $user = \App\User::where('email', $request->email)->get()->first();
        if ($user && \Hash::check($request->password, $user->password)) // The passwords match...
        {
            $token = self::getToken($request->email, $request->password);            
            $response = ['success'=>true, 'data'=>['id'=>$user->id,'auth_token'=>$token,'name'=>$user->name, 'email'=>$user->email]];           
        }
        else 
          $response = ['success'=>false, 'data'=>'Record doesnt exists'];
      

        return response()->json($response, 201);
    }

    
    public function register(Request $request)
    {        
        //The token is passed as authorization bearer
        $payload = [
            'password'=>\Hash::make($request->password),
            'email'=>$request->email,
            'name'=>$request->name           
        ];
                  
        $user = new \App\User($payload);
        if ($user->save())
        {
            
            $token = self::getToken($request->email, $request->password); // generate user token
            
            if (!is_string($token))  return response()->json(['success'=>false,'data'=>'Token generation failed'], 201);
               
            $response = ['success'=>true, 'data'=>['name'=>$user->name,'id'=>$user->id,'email'=>$request->email,'auth_token'=>$token]];        
        }
        else
            $response = ['success'=>false, 'data'=>'Couldnt register user'];
        
        
        return response()->json($response, 201);
    }

    public function getAuthenticatedUser()
    {
        try {

                if (! $user = JWTAuth::parseToken()->authenticate()) {
                        return response()->json(['user_not_found'], 404);
                }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

                return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

                return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

                return response()->json(['token_absent'], $e->getStatusCode());

        }

        return response()->json(compact('user'));
    }

}