<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TicketType;
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

        foreach ($events as $event) {
            $event->ticket_sold = $event->ticketTypes()->sum('sold_quantity');
        }

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

        $events->where('status', 'published');

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

        foreach ($events as $event) {
            $event->ticket_sold = $event->ticketTypes()->sum('sold_quantity');
        }

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

    public function show($slug)
    {
        $event = Event::with('organizer', 'category', 'ticketTypes')->where('slug', $slug)->first();

        if (!$event) {
            return response()->json(
                [
                    'message' => 'Event not found',
                    'statusCode' => 404,
                ],
                404,
                [],
                JSON_UNESCAPED_SLASHES
            )->setStatusCode(404, 'Not Found');
        }

        $event->ticket_sold = $event->ticketTypes()->sum('sold_quantity');

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'gallery' => 'nullable|array',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
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
            'ticket_types' => 'required|array',
            'ticket_types.*.name' => 'required|string|max:255',
            'ticket_types.*.description' => 'nullable|string',
            'ticket_types.*.price' => 'required|numeric|min:0',
            'ticket_types.*.quantity' => 'required|integer|min:0',
            'ticket_types.*.sale_start_date' => 'required|date',
            'ticket_types.*.sale_end_date' => 'required|date|after:sale_start_date',
            'ticket_types.*.benefits' => 'nullable|string',
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

            if ($request->has('ticket_types')) {
                foreach ($request->ticket_types as $ticketTypeData) {
                    $ticketTypeData['event_id'] = $event->id;
                    TicketType::create($ticketTypeData);
                }
            }

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'gallery' => 'nullable|array', // Ini akan menjadi array dari UploadedFile objects baru
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
            'remove_gallery_images' => 'nullable|array', // Ini akan menjadi array dari URL string untuk dihapus
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
            'tags' => 'nullable|array', // Ini akan menjadi array dari string tags
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
            // Karena ada accessor di model, $event->gallery sudah berupa array URL lengkap.
            // Kita perlu mengonversinya kembali ke path relatif untuk perbandingan dan penyimpanan.
            $currentRelativeGalleryPaths = [];
            if (!empty($event->getRawOriginal('gallery'))) { // Ambil nilai asli dari DB
                $decodedGallery = json_decode($event->getRawOriginal('gallery'), true);
                if (is_array($decodedGallery)) {
                    $currentRelativeGalleryPaths = array_filter($decodedGallery);
                }
            }


            // Remove specified gallery images
            if ($request->has('remove_gallery_images') && is_array($request->remove_gallery_images)) {
                foreach ($request->remove_gallery_images as $imageToRemoveUrl) {
                    // Konversi URL lengkap dari frontend kembali ke path relatif untuk perbandingan
                    $relativePathToRemove = Str::after($imageToRemoveUrl, asset('storage/'));

                    if (($key = array_search($relativePathToRemove, $currentRelativeGalleryPaths)) !== false) {
                        Storage::disk('public')->delete($relativePathToRemove); // Hapus file fisik
                        unset($currentRelativeGalleryPaths[$key]); // Hapus dari array
                    }
                }
                $currentRelativeGalleryPaths = array_values($currentRelativeGalleryPaths); // Re-index array
            }

            // Add new gallery images
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $file) {
                    $currentRelativeGalleryPaths[] = $this->uploadImage($file, 'events/gallery'); // Upload dan tambahkan path relatif
                }
            }

            // Encode kembali array path relatif menjadi string JSON sebelum disimpan
            $eventData['gallery'] = !empty($currentRelativeGalleryPaths) ? json_encode($currentRelativeGalleryPaths) : null;

            // Handle tags
            if ($request->has('tags')) {
                // $request->tags sudah berupa array dari string (dari frontend tags[])
                // Langsung encode array ini menjadi string JSON untuk disimpan
                $eventData['tags'] = json_encode($request->tags);
            }

            $event->update($eventData); // Lakukan update pada model Event

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'data' => $event->fresh()->load(['organizer', 'category']) // Muat ulang relasi jika perlu
            ], 200);
        } catch (\Exception $e) {
            // Tangani error secara umum dan berikan detail lebih lanjut untuk debugging
            return response()->json([
                'success' => false,
                'message' => 'Failed to update event',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
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
                // $event->image should already be the relative path
                Storage::disk('public')->delete($event->image);
            }

            // Delete gallery images
            // Get the raw JSON string from the database, bypassing the accessor
            $rawGallery = $event->getRawOriginal('gallery');

            if ($rawGallery) {
                // Decode the raw JSON string into an array of relative paths
                $galleryImages = json_decode($rawGallery, true);

                // Ensure the decoded result is actually an array before looping
                if (is_array($galleryImages)) {
                    foreach ($galleryImages as $image) {
                        // Ensure the image path is not null or empty before attempting deletion
                        if ($image) {
                            Storage::disk('public')->delete($image);
                        }
                    }
                }
            }

            $event->delete(); // Delete the event record itself

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
