<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogoutController extends Controller
{
    //

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->tokens()->delete();
            return response()->json([
                'message' => 'Logout successful',
                'statusCode' => 200,
            ])->setStatusCode(200, 'OK');
        }

        return response()->json([
            'message' => 'User not authenticated',
            'statusCode' => 401,
        ])->setStatusCode(401, 'Unauthorized');
    }
}
