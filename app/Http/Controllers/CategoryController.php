<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    //

    public function index()
    {
        $categories = Category::all();
        return response()->json([
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
            'statusCode' => 200,
        ])->setStatusCode(200, 'OK');
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json([
            'message' => 'Category retrieved successfully',
            'data' => $category,
            'statusCode' => 200,
        ])->setStatusCode(200, 'OK');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $slug = Str::slug($request->name, '-');

        // Check if the slug already exists
        $existingCategory = Category::where('slug', $slug)->first();

        if ($existingCategory) {
            return response()->json([
                'message' => 'Category with this slug already exists',
                'statusCode' => 409,
            ])->setStatusCode(409, 'Conflict');
        }

        $category = Category::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category,
            'statusCode' => 201,
        ])->setStatusCode(201, 'Created');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $slug = Str::slug($request->name, '-');

        // Check if the slug already exists for another category
        $existingCategory = Category::where('slug', $slug)->where('id', '!=', $id)->first();

        if ($existingCategory) {
            return response()->json([
                'message' => 'Category with this slug already exists',
                'statusCode' => 409,
            ])->setStatusCode(409, 'Conflict');
        }

        $category->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category,
            'statusCode' => 200,
        ])->setStatusCode(200, 'OK');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
            'statusCode' => 200,
        ])->setStatusCode(200, 'OK');
    }

    public function events($id)
    {
        $category = Category::findOrFail($id);
        $events = $category->events;

        return response()->json([
            'message' => 'Events retrieved successfully',
            'data' => $events,
            'statusCode' => 200,
        ])->setStatusCode(200, 'OK');
    }
}
