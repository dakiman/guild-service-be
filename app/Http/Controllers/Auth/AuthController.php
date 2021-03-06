<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use Auth;
use Response;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|min:2|max:125',
            'email' => 'required|unique:users',
            'password' => 'required|min:8'
        ]);

        $user = User::create($request->all());

        $token = auth()->login($user);

        return $this->respondWithToken($token);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $credentials = $request->only(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Invalid email or password entered.'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function user()
    {
        return response()->json([
            'user' =>  User::with('characters')->find(auth()->id())
        ]);
    }

    private function respondWithToken($token)
    {
        return response()->json([
            'accessToken' => $token,
            'tokenType' => 'bearer',
            'expiresIn' => Auth::factory()->getTTL() * 60,
            'user' => User::with('characters')->find(auth()->id()),
        ]);
    }
}
