<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    //

    public function profile(Request $request)
    {
        // Return the authenticated user's profile
        return response()->json([
            'data' => $request->user(),
            'message' => 'User profile retrieved successfully',
            'statusCode' => 200,
        ])->setStatusCode(200, 'OK');
    }
}
