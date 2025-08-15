<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:6|confirmed',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'Validation Errors.',
                'errors'    => $validator->errors()
            ], 422);
        }

        $profileImage = null;
        if ($request->hasFile('profile_image')) {
            $profileImage = $request->file('profile_image')->store('profile_image', 'public');
        }

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'profile_image' => $profileImage,
        ]);

        $token = $user->createToken('API TOKEN')->plainTextToken;

        return response()->json([
            'status'    => true,
            'message'   => 'User Registered Succesfully.',
            'token'     => $token,
            'user'      => $user,
        ], 201);
    }

    // Login (Email/Password)
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => 'Validation Error.',
                'errors'    => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'    => false,
                'message'   => 'Invalid Email or Password.'
            ], 401);
        }

        $token = $user->createToken('API TOKEN')->plainTextToken;

        return response()->json([
            'status'    => true,
            'message'   => 'Login Succesful.',
            'token'     => $token,
            'user'      => $user,
        ], 200);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Logged Out Succesfully.',
        ], 200);
    }

    // Google API
public function redirectToProvider()
{
    return Socialite::driver('google')->redirect();
}

// Google Callback
public function handleProviderCallback()
{
    try {
        $socialUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('provider_id', $socialUser->getId())
                    ->orWhere('email', $socialUser->getEmail())
                    ->first();

        if (!$user) {
            $user = User::create([
                'name'          => $socialUser->getName(),
                'email'         => $socialUser->getEmail(),
                'provider'      => 'google',
                'provider_id'   => $socialUser->getId(),
                'profile_image' => $socialUser->getAvatar(),
                'password'      => Hash::make(Str::random(10)),
            ]);
        }

        $token = $user->createToken('API TOKEN')->plainTextToken;

        return redirect()->route('dashboard')->with('auth_token', $token);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Authentication failed.',
            'error'   => $e->getMessage()
        ], 500);
    }
}
}
