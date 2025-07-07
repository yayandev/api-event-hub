<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Transaction;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmail;

class MidtransController extends Controller
{
    public function callback(Request $request)
    {
        try {
            $serverKey = env('MIDTRANS_SERVER_KEY');
            $hashedKey = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

            if ($hashedKey !== $request->signature_key) {
                return response()->json(['message' => 'Invalid signature key'], 403);
            }

            $transactionStatus = $request->transaction_status;
            $orderIdArray = explode('_', $request->order_id);
            $orderId = $orderIdArray[0];
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            switch ($transactionStatus) {
                case 'capture':
                    if ($request->payment_type == 'credit_card') {
                        if ($request->fraud_status == 'challenge') {
                            $order->update(['status' => 'pending']);
                            $order->payments()->update(['status' => 'pending']);
                        } else {
                            $order->update(['status' => 'paid', 'paid_at' => now(), 'payment_method' => $request->payment_type ?? null]);
                            $order->payments()->update(['status' => 'paid']);
                            //update ticket type reserved quantity and sold quantity
                            foreach ($order->items as $item) {
                                $ticketType = $item->ticketType;
                                $ticketType->reserved_quantity -= $item->quantity;
                                $ticketType->sold_quantity += $item->quantity;
                                $ticketType->save();
                            }

                            $sendMail = Mail::to($order->user->email)->send(new SendEmail($order));
                        }
                    }
                    break;

                case 'settlement':
                    $order->update(['status' => 'paid', 'paid_at' => now(), 'payment_method' => $request->payment_type ?? null]);
                    $order->payments()->update(['status' => 'paid']);
                    //update ticket type reserved quantity and sold quantity
                    foreach ($order->items as $item) {
                        $ticketType = $item->ticketType;
                        $ticketType->reserved_quantity -= $item->quantity;
                        $ticketType->sold_quantity += $item->quantity;
                        $ticketType->save();
                    }

                    $sendMail = Mail::to($order->user->email)->send(new SendEmail($order));
                    break;

                case 'pending':
                    $order->update(['status' => 'pending']);
                    $order->payments()->update(['status' => 'pending']);
                    break;

                case 'deny':
                    $order->update(['status' => 'failed']);
                    $order->payments()->update(['status' => 'failed']);
                    break;

                case 'expire':
                    $order->update(['status' => 'expired', 'expired_at' => now()]);
                    $order->payments()->update(['status' => 'failed']);
                    break;

                case 'cancel':
                    $order->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                    $order->payments()->update(['status' => 'cancelled']);
                    break;

                default:
                    $order->update(['status' => 'unknown']);
                    break;
            }

            return response()->json(['message' => 'Callback received successfully']);
        } catch (\Exception $e) {
            // Log lengkap untuk keperluan debugging
            Log::error('Midtrans Callback Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function sendEmailInvoice(Request $request, $id)
    {
        $order = Order::with(['items.ticketType', 'event', 'user', 'payments'])->findOrFail($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }


        $sendMail = Mail::to($order->user->email)->send(new SendEmail($order));

        return $sendMail;
    }
}
