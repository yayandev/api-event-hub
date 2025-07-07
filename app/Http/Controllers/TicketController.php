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

        $tickets = [];

        foreach ($items as $item) {
            $existingTickets = $order->tickets()->where('ticket_type_id', $item->ticket_type_id)->get();
            $remainingQuantity = $item->quantity - $existingTickets->count();

            if ($remainingQuantity > 0) {
                for ($i = 0; $i < $remainingQuantity; $i++) {
                    $ticket = $order->generateTicket($item->ticket_type_id);
                    array_push($tickets, $ticket);
                }
            } else {
                array_push($tickets, $existingTickets);
            }
        }

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
