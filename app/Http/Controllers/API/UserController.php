<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // user profile by ID
    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'status' => true,
            'user' => $user
        ]);
    }

    // Update Personal Info (only own profile)
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user->name = $request->name;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile Updated Successfully.',
            'user' => $user
        ]);
    }

    // Change Profile Picture
    public function updateProfilePicture(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Delete Old Image
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $path = $request->file('profile_image')->store('profile_image', 'public');
        $user->profile_image = $path;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile Picture Updated Successfully.',
            'user' => $user
        ]);
    }
}
