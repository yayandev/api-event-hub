<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UpdateProfileController extends Controller
{
    //

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
            'phone' => 'required|unique:users,phone,' . $request->user()->id,
        ]);

        $user = User::find($request->user()->id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        return response()->json(['message' => 'Profile updated successfully'], 200);
    }
}
