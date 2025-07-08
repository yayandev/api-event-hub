<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    //

    public function index(Request $request)
    {
        if ($request->user()->hasRole('admin')) {
            $events = Event::query()->with('organizer', 'category');
        } else {
            $events = $request->user()->events()->with('organizer', 'category');
        }

        if ($request->has('title')) {
            $events->where('title', 'like', '%' . $request->input('title') . '%');
        }

        if ($request->has('start')) {
            $events->where('start', '>=', $request->input('start'));
        }

        if ($request->has('end')) {
            $events->where('end', '<=', $request->input('end'));
        }

        $events = $events->paginate(10);

        return response()->json([
            'data' => $events->items(),
            'meta' => [
                'total' => $events->total(),
                'per_page' => $events->perPage(),
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'from' => $events->firstItem(),
                'to' => $events->lastItem(),
            ],
            'message' => 'Events retrieved successfully',
            'statusCode' => 200,
        ])->setStatusCode(200, 'OK');
    }

    public function publicIndex(Request $request)
    {
        $events = Event::with('organizer', 'category');

        if ($request->has('title')) {
            $events->where('title', 'like', '%' . $request->input('title') . '%');
        }

        if ($request->has('start')) {
            $events->where('start', '>=', $request->input('start'));
        }

        if ($request->has('end')) {
            $events->where('end', '<=', $request->input('end'));
        }

        if ($request->has('category_id')) {
            $events->where('category_id', $request->category_id);
        }

        $events = $events->paginate(10);

        return response()->json(
            [
                'data' => $events->items(),
                'meta' => [
                    'total' => $events->total(),
                    'per_page' => $events->perPage(),
                    'current_page' => $events->currentPage(),
                    'last_page' => $events->lastPage(),
                    'from' => $events->firstItem(),
                    'to' => $events->lastItem(),
                ],
                'message' => 'Public events retrieved successfully',
                'statusCode' => 200,
            ],
            200,
            [],
            JSON_UNESCAPED_SLASHES
        )->setStatusCode(200, 'OK');
    }

    public function show($id)
    {
        $event = Event::with('category', 'organizer', 'ticketTypes')->findOrFail($id);

        return response()->json(
            [
                'data' => $event,
                'message' => 'Event retrieved successfully',
                'statusCode' => 200,
            ],
            200,
            [],
            JSON_UNESCAPED_SLASHES
        )->setStatusCode(200, 'OK');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'location' => 'required|string|max:255',
            'venue' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'status' => 'nullable|in:draft,published,cancelled,completed',
            'max_capacity' => 'required|integer|min:1',
            'is_free' => 'boolean',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0|gte:min_price',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'terms_and_conditions' => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $eventData = $request->only([
                'category_id',
                'title',
                'description',
                'short_description',
                'location',
                'venue',
                'address',
                'latitude',
                'longitude',
                'start_datetime',
                'end_datetime',
                'max_capacity',
                'is_free',
                'min_price',
                'max_price',
                'terms_and_conditions',
                'is_featured'
            ]);

            //organizer_id is set to the authenticated user's ID
            $eventData['organizer_id'] = $request->user()->id;

            // Generate slug
            $eventData['slug'] = $this->generateUniqueSlug($request->title);

            // Set status
            $eventData['status'] = $request->status ?? 'draft';

            // Set published_at if status is published
            if ($eventData['status'] === 'published') {
                $eventData['published_at'] = now();
            }

            // Handle main image upload
            if ($request->hasFile('image')) {
                $eventData['image'] = $this->uploadImage($request->file('image'), 'events/main');
            }

            // Handle gallery images upload
            $galleryImages = [];
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $file) {
                    $galleryImages[] = $this->uploadImage($file, 'events/gallery');
                }
            }
            $eventData['gallery'] = !empty($galleryImages) ? json_encode($galleryImages) : null;

            // Handle tags
            if ($request->has('tags')) {
                $eventData['tags'] = json_encode($request->tags);
            }

            $event = Event::create($eventData);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully',
                'data' => $event->load(['organizer', 'category'])
            ], 201);
        } catch (\Exception $e) {
            // Clean up uploaded files if event creation fails
            if (isset($eventData['image'])) {
                Storage::disk('public')->delete($eventData['image']);
            }
            if (!empty($galleryImages)) {
                foreach ($galleryImages as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, Event $event): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|exists:categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'short_description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_gallery_images' => 'nullable|array',
            'remove_gallery_images.*' => 'string',
            'location' => 'sometimes|string|max:255',
            'venue' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'start_datetime' => 'sometimes|date',
            'end_datetime' => 'sometimes|date|after:start_datetime',
            'status' => 'sometimes|in:draft,published,cancelled,completed',
            'max_capacity' => 'nullable|integer|min:1',
            'is_free' => 'boolean',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0|gte:min_price',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'terms_and_conditions' => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $eventData = $request->only([
                'category_id',
                'title',
                'description',
                'short_description',
                'location',
                'venue',
                'address',
                'latitude',
                'longitude',
                'start_datetime',
                'end_datetime',
                'max_capacity',
                'is_free',
                'min_price',
                'max_price',
                'terms_and_conditions',
                'is_featured',
                'status'
            ]);

            // Update slug if title is changed
            if ($request->has('title')) {
                $eventData['slug'] = $this->generateUniqueSlug($request->title, $event->id);
            }

            // Set published_at if status is changed to published
            if ($request->has('status') && $request->status === 'published' && $event->status !== 'published') {
                $eventData['published_at'] = now();
            }

            // Handle main image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($event->image) {
                    Storage::disk('public')->delete($event->image);
                }
                $eventData['image'] = $this->uploadImage($request->file('image'), 'events/main');
            }

            // Handle gallery images
            $currentGallery = $event->gallery ? json_decode($event->gallery, true) : [];

            // Remove specified gallery images
            if ($request->has('remove_gallery_images')) {
                foreach ($request->remove_gallery_images as $imageToRemove) {
                    if (($key = array_search($imageToRemove, $currentGallery)) !== false) {
                        Storage::disk('public')->delete($imageToRemove);
                        unset($currentGallery[$key]);
                    }
                }
                $currentGallery = array_values($currentGallery); // Reindex array
            }

            // Add new gallery images
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $file) {
                    $currentGallery[] = $this->uploadImage($file, 'events/gallery');
                }
            }

            $eventData['gallery'] = !empty($currentGallery) ? json_encode($currentGallery) : null;

            // Handle tags
            if ($request->has('tags')) {
                $eventData['tags'] = json_encode($request->tags);
            }

            $event->update($eventData);

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'data' => $event->fresh()->load(['organizer', 'category'])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload image and return the path
     */
    private function uploadImage($file, $directory): string
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');
        return $path;
    }

    /**
     * Generate unique slug for event
     */
    private function generateUniqueSlug($title, $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $query = Event::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function destroy(Event $event): JsonResponse
    {
        try {
            // Delete main image
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }

            // Delete gallery images
            if ($event->gallery) {
                $galleryImages = json_decode($event->gallery, true);
                foreach ($galleryImages as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete event',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
