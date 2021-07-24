<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Users;

class AuthController extends Controller

{




    /**

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */

    // API for authentication/login


    public function authenticate(Request $request)
    {

        $this->validate($request, [

            'email' => 'required',

            'password' => 'required'

        ]);

        // dd($request);
        $credentials = $request->only(['email', 'password']);
        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json(['message' => 'User ID and Password are incorrect'], 404);
        }
        $user = Auth::user();
        return response()->json(['user' => $user, 'token' => $token, 'message' => 'CREATED'], 200);
    }

    //User Registration APIs
    public function register(Request $request)
    {

        $this->validate($request, [

            'first_name' => 'required',
            'last_name' => 'required',

            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        $user = new Users;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;


        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        $credentials = $request->only(['email', 'password']);
        $token = Auth::attempt($credentials);

        return response()->json(['user' => $user, 'token' => $token, 'message' => 'CREATED'], 201);
    }
}
