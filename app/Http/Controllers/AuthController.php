<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $r)
    {
        $r->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $r->email)->first();

        if (!$user || !Hash::check($r->password, $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 422);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user,
        ]);
    }

    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Sesión cerrada']);
    }

    public function register(Request $r)
    {
        $data = $r->validate([
            'name'      => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => ['required', \Illuminate\Validation\Rules\Password::min(8)],
        ]);

        $user = \App\Models\User::create([
            'name'      => $data['name'],
            'last_name' => $data['last_name'],
            'email'     => $data['email'],
            'password'  => bcrypt($data['password']),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user], 201);
    }
}
