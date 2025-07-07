<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TicketTypeController extends Controller
{
    /**
     * Store a newly created ticket type in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'event_id' => 'required|exists:events,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0|max:999999999999.99',
                'quantity' => 'required|integer|min:1',
                'min_purchase' => 'nullable|integer|min:1',
                'max_purchase' => 'nullable|integer|min:1',
                'sale_start_date' => 'required|date',
                'sale_end_date' => 'required|date|after:sale_start_date',
                'is_active' => 'nullable|boolean',
                'sort_order' => 'nullable|integer|min:0',
                'benefits' => 'nullable|array',
                'benefits.*' => 'string'
            ]);

            // Set default values
            $validated['sold_quantity'] = 0;
            $validated['reserved_quantity'] = 0;
            $validated['min_purchase'] = $validated['min_purchase'] ?? 1;
            $validated['max_purchase'] = $validated['max_purchase'] ?? 10;
            $validated['is_active'] = $validated['is_active'] ?? true;
            $validated['sort_order'] = $validated['sort_order'] ?? 0;

            // Validate min_purchase <= max_purchase
            if ($validated['min_purchase'] > $validated['max_purchase']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Min purchase cannot be greater than max purchase'
                ], 422);
            }

            // Convert benefits array to JSON
            if (isset($validated['benefits'])) {
                $validated['benefits'] = json_encode($validated['benefits']);
            }

            $ticketType = TicketType::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Ticket type created successfully',
                'data' => $ticketType->load('event')
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ticket type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified ticket type in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $ticketType = TicketType::findOrFail($id);

            $validated = $request->validate([
                'event_id' => 'sometimes|exists:events,id',
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'price' => 'sometimes|numeric|min:0|max:999999999999.99',
                'quantity' => 'sometimes|integer|min:1',
                'min_purchase' => 'sometimes|integer|min:1',
                'max_purchase' => 'sometimes|integer|min:1',
                'sale_start_date' => 'sometimes|date',
                'sale_end_date' => 'sometimes|date',
                'is_active' => 'sometimes|boolean',
                'sort_order' => 'sometimes|integer|min:0',
                'benefits' => 'sometimes|array',
                'benefits.*' => 'string'
            ]);

            // Custom validation for sale dates
            if (isset($validated['sale_start_date']) || isset($validated['sale_end_date'])) {
                $startDate = $validated['sale_start_date'] ?? $ticketType->sale_start_date;
                $endDate = $validated['sale_end_date'] ?? $ticketType->sale_end_date;

                if (strtotime($endDate) <= strtotime($startDate)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sale end date must be after sale start date'
                    ], 422);
                }
            }

            // Validate min_purchase <= max_purchase
            if (isset($validated['min_purchase']) || isset($validated['max_purchase'])) {
                $minPurchase = $validated['min_purchase'] ?? $ticketType->min_purchase;
                $maxPurchase = $validated['max_purchase'] ?? $ticketType->max_purchase;

                if ($minPurchase > $maxPurchase) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Min purchase cannot be greater than max purchase'
                    ], 422);
                }
            }

            // Validate quantity against sold tickets
            if (isset($validated['quantity'])) {
                if ($validated['quantity'] < $ticketType->sold_quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Quantity cannot be less than sold quantity (' . $ticketType->sold_quantity . ')'
                    ], 422);
                }
            }

            // Convert benefits array to JSON
            if (isset($validated['benefits'])) {
                $validated['benefits'] = json_encode($validated['benefits']);
            }

            $ticketType->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Ticket type updated successfully',
                'data' => $ticketType->load('event')
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket type not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ticket type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified ticket type from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $ticketType = TicketType::findOrFail($id);

            // Check if there are sold tickets
            if ($ticketType->sold_quantity > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete ticket type with sold tickets'
                ], 422);
            }

            // Check if there are reserved tickets
            if ($ticketType->reserved_quantity > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete ticket type with reserved tickets'
                ], 422);
            }

            $ticketType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ticket type deleted successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket type not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete ticket type',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available quantity for a ticket type.
     */
    public function getAvailableQuantity($id): JsonResponse
    {
        try {
            $ticketType = TicketType::findOrFail($id);

            $availableQuantity = $ticketType->quantity - $ticketType->sold_quantity - $ticketType->reserved_quantity;

            return response()->json([
                'success' => true,
                'data' => [
                    'ticket_type_id' => $ticketType->id,
                    'total_quantity' => $ticketType->quantity,
                    'sold_quantity' => $ticketType->sold_quantity,
                    'reserved_quantity' => $ticketType->reserved_quantity,
                    'available_quantity' => max(0, $availableQuantity)
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket type not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get available quantity',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
