<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    //

    public function createPayment(Request $request)
    {
        //

        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        //get user
        $user = $request->user();

        // Find the order
        $order = Order::findOrFail($request->input('order_id'));

        // Check if the order belongs to the user
        if ($order->user_id !== $user->id) {
            return response()->json(['message' => 'Order does not belong to the user.', 'statusCode' => 400], 400);
        }

        // Check if the order is pending
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Order is not pending.', 'statusCode' => 400], 400);
        }

        //check apakah sudah ada payment
        if ($order->payments->count() > 0) {
            $order->payments()->delete();
        }


        //check discount code
        // if ($order->discount_code) {
        //     $discount = $order->discount_code->discount;
        //     $fixedTotal = $order->total - $discount;
        // } else {
        //     $fixedTotal = $order->total;
        // }

        $total = 0;
        $orderItems = [];

        foreach ($order->items as $item) {
            $itemTotal = (float)$item->price * $item->quantity;
            $total += $itemTotal;

            $orderItems[] = [
                'id' => $item->id,
                'price' => (float)$item->price,
                'quantity' => $item->quantity,
                'name' => $order->event->name . ' - ' . $item->ticketType->name,
            ];
        }

        // Gunakan hasil kalkulasi total sebagai gross_amount
        $orderData = [
            'transaction_details' => [
                'order_id' => $order->id . '_' . substr(Str::uuid(), 0, 8),
                'gross_amount' => $total,
            ],
            'customer_details' => [
                'first_name' => $order->user->name,
                'last_name' => '',
                'email' => $order->user->email,
                'phone' => $order->user->phone ?? '',
            ],
            'item_details' => $orderItems,
        ];


        //create snaptoken midtrans using midtrans service
        $midtrans = new MidtransService();
        $snapToken = $midtrans->createTransaction($orderData);

        //create payment history
        $payment = $order->payments()->create([
            'order_id' => $order->id,
            'payment_reference' => $snapToken->token,
            'gateway_reference' => $snapToken->redirect_url,
            'amount' => $total,
            'currency' => 'IDR',
            'status' => 'pending',
            'gateway_response' => json_encode($snapToken),
            'payment_method' => 'midtrans',
        ]);


        return response()->json([
            'data' => [
                'snap_token' => $snapToken,
                'payment' => $payment
            ],
            'message' => 'Payment created successfully',
            'statusCode' => 201,
        ]);
    }


    public function paymentSuccess(Request $request)
    {
        if (!$request->input('order_id')) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $orderIdArray = explode('_', $request->input('order_id'));

        $order = Order::with(['items.ticketType', 'event', 'user', 'payments'])->findOrFail($orderIdArray[0]);


        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        foreach ($order->items as $item) {
            $item->tickets = \App\Models\Ticket::where('order_id', $item->order_id)
                ->where('ticket_type_id', $item->ticket_type_id)
                ->get();
        }

        return view('payment_success', compact('order'));
    }

    public function paymentError(Request $request)
    {

        if (!$request->input('order_id')) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $orderIdArray = explode('_', $request->input('order_id'));

        $order = Order::with(['items.ticketType', 'event', 'user', 'payments'])->findOrFail($orderIdArray[0]);


        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        foreach ($order->items as $item) {
            $item->tickets = \App\Models\Ticket::where('order_id', $item->order_id)
                ->where('ticket_type_id', $item->ticket_type_id)
                ->get();
        }

        return view('payment_error', compact('order'));
    }
}
