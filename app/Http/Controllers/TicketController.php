<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
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

    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('customer')) {
            // Tiket milik customer (berdasarkan user_id pada order)
            $tickets = Ticket::whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with(['order', 'ticketType'])->latest()->paginate(10);

            return response()->json([
                'data' => $tickets,
                'message' => 'Tickets found',
                'statusCode' => 200
            ]);
        }

        if ($user->hasRole('organizer')) {
            // TicketType yang dibuat oleh organizer
            $ticketTypes = TicketType::whereHas('event', function ($query) use ($user) {
                $query->where('organizer_id', $user->id);
            })->with('event')->latest()->paginate(10);

            return response()->json([
                'data' => $ticketTypes,
                'message' => 'Ticket types found',
                'statusCode' => 200
            ]);
        }

        if ($user->hasRole('admin')) {
            // Semua tiket dari semua customer
            $tickets = Ticket::with(['order.user', 'ticketType.event'])->latest()->paginate(10);

            return response()->json([
                'data' => $tickets,
                'message' => 'Tickets found',
                'statusCode' => 200
            ]);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    public function show(Request $request, $identifier)
    {
        $user = $request->user();

        if ($user->hasRole('customer')) {
            // Customer: lihat tiket miliknya berdasarkan ticket_code
            $ticket = Ticket::where('ticket_code', $identifier)
                ->with(['order.user', 'ticketType.event'])
                ->first();

            if (!$ticket || $ticket->order->user_id !== $user->id) {
                return response()->json(['message' => 'Ticket not found or unauthorized'], 404);
            }

            return response()->json([
                'data' => $ticket,
                'message' => 'Ticket found',
                'statusCode' => 200
            ]);
        }

        if ($user->hasRole('organizer')) {
            // Organizer: lihat ticket type yang dia buat berdasarkan ID
            $ticketType = TicketType::where('id', $identifier)
                ->whereHas('event', function ($q) use ($user) {
                    $q->where('organizer_id', $user->id);
                })
                ->with('event')
                ->first();

            if (!$ticketType) {
                return response()->json(['message' => 'Ticket Type not found or unauthorized'], 404);
            }

            return response()->json([
                'data' => $ticketType,
                'message' => 'Ticket Type found',
                'statusCode' => 200
            ]);
        }

        if ($user->hasRole('admin')) {
            // Admin: bisa lihat semua tiket berdasarkan ticket_code
            $ticket = Ticket::where('ticket_code', $identifier)
                ->with(['order.user', 'ticketType.event'])
                ->first();

            if (!$ticket) {
                return response()->json(['message' => 'Ticket not found'], 404);
            }

            return response()->json([
                'data' => $ticket,
                'message' => 'Ticket found',
                'statusCode' => 200
            ]);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
