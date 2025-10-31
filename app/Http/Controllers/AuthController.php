<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $r)
    {
        $cred = $r->validate(['email' => 'required|email', 'password' => 'required']);
        if (!Auth::attempt($cred))
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        $token = $r->user()->createToken('web')->plainTextToken;
        return ['token' => $token, 'user' => Auth::user()];
    }
    public function me(Request $r)
    {
        return $r->user();
    }
    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()->delete();
        return ['ok' => true];
    }

}
