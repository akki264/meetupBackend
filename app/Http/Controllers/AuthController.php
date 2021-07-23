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

    public function __construct()

    {

        //  $this->middleware('auth:api');

    }

    /**

     * Display a listing of the resource.

     *

     * @return \Illuminate\Http\Response

     */

    public function authenticate(Request $request)
    {

        $this->validate($request, [

            'email' => 'required',

            'password' => 'required'

        ]);

        $credentials = $request->only(['email', 'password']);
        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user = Auth::user();
        return response()->json(['user' => $user, 'token' => $token, 'message' => 'CREATED'], 200);
    }

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
