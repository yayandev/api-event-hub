<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    //

    public function createTicket(Request $request, $orderId)
    {
        $order = Order::find($orderId);


        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $user = $request->user();

        if ($user->id !== $order->user_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        if ($order->status !== 'paid') {
            return response()->json(['message' => 'Order is not paid'], 400);
        }

        $items = $order->items()->get();


        $tickets = $order->tickets()->get()->merge(collect($items)->flatMap(function ($item) use ($order) {
            $existingTickets = $order->tickets()->where('ticket_type_id', $item->ticket_type_id)->get();
            $remainingQuantity = $item->quantity - $existingTickets->count();

            if ($remainingQuantity > 0) {
                return collect(range(0, $remainingQuantity - 1))->map(function () use ($order, $item) {
                    return $order->generateTicket($item->ticket_type_id);
                });
            }

            return $existingTickets;
        }));

        return response()->json([
            'data' => $tickets,
            'message' => 'Tickets created successfully',
            'statusCode' => 200
        ]);
    }

    public function checkInTicket(Request $request, $ticket_code)
    {
        $ticket = \App\Models\Ticket::where('ticket_code', $ticket_code)->first();

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        if ($ticket->status === 'used') {
            return response()->json(['message' => 'Ticket is already checked in', 'statusCode' => 400], 400);
        }

        $ticket->status = 'used';
        $ticket->checked_in_at = now();
        $ticket->checked_in_by = $request->user()->id;
        $ticket->save();

        return response()->json([
            'data' => $ticket,
            'message' => 'Ticket checked in successfully',
            'statusCode' => 200
        ]);
    }
}
