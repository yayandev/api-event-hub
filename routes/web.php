<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/docs/api');
});

Route::get('/payment_success', [PaymentController::class, 'paymentSuccess'])
    ->name('payment.success');

Route::get('/payment_error', [PaymentController::class, 'paymentError'])
    ->name('payment.error');
