<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/docs/api');
});

Route::get('/payment_success', [PaymentController::class, 'paymentSuccess'])
    ->name('payment.success');

Route::get('/payment_error', [PaymentController::class, 'paymentError'])
    ->name('payment.error');

Route::get('/order-tikets/{order_number}', [OrderController::class, 'tickets'])->name('order-tikets');
