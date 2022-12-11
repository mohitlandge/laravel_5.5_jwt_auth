<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;

class UserController extends Controller
{
    
    public function getUserList(){

        $users = User::all();

        return response()->json(['users' => $users]);
    }

    public function register(Request $request){

        $validator = Validator::make($request->all(),[
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|max:15|same:confirmed'
        ]);

        if($validator->fails()){

            return response()->json($validator->errors());

        }


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);


        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ]);
    }


    public function login(Request $request){


        $validator = Validator::make($request->all(),[
            'email' => 'required|string|email',
            'password' => 'required|string|min:6|max:15'
        ]);

        if($validator->fails()){

            return response()->json($validator->errors());

        }

        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials); 

        if (!$token) {

            return response()->json(['error' => 'Unauthorised']);

        }

        return $this->respondWithToken($token);
    }


        protected function respondWithToken($token){

            return response()->json([

                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL()*60
            ]);
        }


        public function profile(){

            return response()->json(auth()->user());
        }


        public function refresh(){

            return $this->respondWithToken(auth()->refresh());
        }


        public function logout(){

            auth()->logout();

            return response()->json(['message' => 'User successfully logged out']);

        }




    
}
