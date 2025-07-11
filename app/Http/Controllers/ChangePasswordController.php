<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    //

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|confirmed|min:8|different:old_password',
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'message' => 'The provided password does not match your current password.',
                'statusCode' => 422
            ], 422);
        }

        $user->password = Hash::make($request->password);

        $user->save();

        return response()->json([
            'message' => 'Password changed successfully.',
            'statusCode' => 200
        ]);
    }
}
