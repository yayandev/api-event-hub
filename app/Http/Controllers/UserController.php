<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //

    public function index(Request $request)
    {
        $users = User::query();

        if ($request->has('name')) {
            $users->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->has('email')) {
            $users->where('email', 'like', '%' . $request->input('email') . '%');
        }

        if ($request->has('role')) {
            $users->where('role', $request->input('role'));
        }

        $users = $users->paginate(10);

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
            'message' => 'Users retrieved successfully',
            'statusCode' => 200,
        ])->setStatusCode(200, 'OK');
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'statusCode' => 404,
            ])->setStatusCode(404, 'Not Found');
        }

        return response()->json([
            'data' => $user,
            'message' => 'User retrieved successfully',
            'statusCode' => 200,
        ])->setStatusCode(200, 'OK');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:15',
            'role' => 'required|in:admin,organizer,customer',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'phone' => $request->input('phone'),
            'role' => $request->input('role'),
        ]);


        return response()->json([
            'data' => $user,
            'message' => 'User created successfully',
            'statusCode' => 201,
        ])->setStatusCode(201, 'Created');
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'statusCode' => 404,
            ])->setStatusCode(404, 'Not Found');
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:15',
            'role' => 'sometimes|required|in:admin,organizer,customer',
        ]);

        $user->name = $request->input('name', $user->name);
        $user->email = $request->input('email', $user->email);

        if ($request->has('password')) {
            $user->password = bcrypt($request->input('password'));
        }

        $user->phone = $request->input('phone', $user->phone);
        $user->role = $request->input('role', $user->role);
        $user->save();

        return response()->json([
            'data' => $user,
            'message' => 'User updated successfully',
            'statusCode' => 200,
        ])->setStatusCode(200, 'OK');
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'statusCode' => 404,
            ])->setStatusCode(404, 'Not Found');
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
            'statusCode' => 200,
        ])->setStatusCode(200, 'OK');
    }
}
