<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Generate unique order number
     */
    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . strtoupper(Str::random(8)) . '-' . date('Ymd');
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Create a new order
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'ticket_type_id' => 'required|exists:ticket_types,id',
            'quantity' => 'required|integer|min:1',
            'discount_code' => 'nullable|exists:discount_codes,code',
        ]);

        // Find the event
        $event = Event::findOrFail($request->input('event_id'));

        // Check if the event is active for sales
        if (!$event->isSaleActive()) {
            return response()->json(['message' => 'Event sales are not active.', 'statusCode' => 400], 400);
        }

        // Find the customer
        $user = $request->user();

        // Validate ticket type availability
        $ticketType = $event->ticketTypes()->findOrFail($request->input('ticket_type_id'));

        // Check if the ticket type is active and available
        if (!$ticketType->getIsSaleActiveAttribute()) {
            return response()->json(['message' => 'Ticket sales for this type are not active.', 'statusCode' => 400], 400);
        }

        // Check if the ticket type has available quantity
        if (!$ticketType->getAvailableQuantityAttribute()) {
            return response()->json(['message' => 'Ticket type is out of stock.', 'statusCode' => 400], 400);
        }

        //create the order
        $order = new Order();
        //data order
        $order->user_id = $user->id;
        $order->event_id = $event->id;
        $order->order_number = $this->generateOrderNumber();
        $order->subtotal = $ticketType->price * $request->input('quantity');
        $order->total_amount = $order->subtotal; // Add tax, fees, etc
        $order->status = 'pending';

        //data buyer
        $order->buyer_name = $user->name;
        $order->buyer_email = $user->email;
        $order->buyer_phone = $user->phone ?? '';
        $order->buyer_address = $user->address ?? '';

        // Optional fields
        $order->notes = $request->input('notes') ?? null;
        $order->discount_code = $request->input('discount_code') ?? null;

        $order->save();

        //create order item
        $orderItem = new OrderItem();
        $orderItem->order_id = $order->id;
        $orderItem->ticket_type_id = $ticketType->id;
        $orderItem->quantity = $request->input('quantity');
        $orderItem->price = $ticketType->price;
        $orderItem->total_price = $ticketType->price * $request->input('quantity');
        $orderItem->save();

        // Update ticket type reserved quantity
        $ticketType->reserved_quantity += $request->input('quantity');
        $ticketType->save();

        return response()->json([
            'message' => 'Order created successfully.',
            'order' => $order->load('items.ticketType'),
            'order_number' => $order->order_number,
            'statusCode' => 201,
        ], 201);
    }

    public function cancelOrder(Request $request, $order_number)
    {
        $order = Order::where('order_number', $order_number)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.', 'statusCode' => 404], 404);
        }

        $order->status = 'cancelled';
        $order->save();

        // Update ticket type reserved quantity
        foreach ($order->items as $item) {
            $ticketType = $item->ticketType;
            $ticketType->reserved_quantity -= $item->quantity;
            $ticketType->save();
        }

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'statusCode' => 200,
        ], 200);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $orders = [];

        if ($user->hasRole('customer')) {
            $orders = $this->getCustomerOrders($user);
        } elseif ($user->hasRole('organizer')) {
            $orders = $this->getOrganizerOrders($user);
        } else {
            $orders = Order::with(['items.ticketType', 'user', 'event.organizer'])->paginate(10);
        }

        return response()->json([
            'orders' => $orders,
            'statusCode' => 200,
            'message' => 'Orders retrieved successfully.',
        ], 200);
    }

    private function getCustomerOrders($user)
    {
        $orders = $user->orders()
            ->with(['items.ticketType', 'event', 'payments'])
            ->paginate(10);

        // Attach tickets secara manual ke tiap item
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $item->tickets = \App\Models\Ticket::where('order_id', $item->order_id)
                    ->where('ticket_type_id', $item->ticket_type_id)
                    ->get();
            }
        }

        return $orders;
    }


    private function getOrganizerOrders($user)
    {
        return Order::whereHas('event', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['items.ticketType', 'user', 'event'])
            ->paginate(10);
    }
}
