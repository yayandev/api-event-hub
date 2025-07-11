<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketTypeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UpdateProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [RegisterController::class, 'register'])
    ->name('register');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login');

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profile', [UserProfileController::class, 'profile'])
        ->name('profile');

    Route::post('/update_profile', [UpdateProfileController::class, 'updateProfile'])
        ->name('update_profile');

    Route::post('/change_password', [ChangePasswordController::class, 'changePassword'])->name('change_password');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/logout', [LogoutController::class, 'logout'])
        ->name('logout');

    //admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::resource('categories', CategoryController::class)
            ->except(['create', 'edit'])
            ->names([
                'index' => 'categories.index',
                'store' => 'categories.store',
                'show' => 'categories.show',
                'update' => 'categories.update',
                'destroy' => 'categories.destroy',
            ]);

        Route::get('/categories/{id}/events', [CategoryController::class, 'events'])
            ->name('categories.events');

        Route::resource('users', UserController::class)
            ->except(['create', 'edit'])
            ->names([
                'index' => 'users.index',
                'store' => 'users.store',
                'show' => 'users.show',
                'update' => 'users.update',
                'destroy' => 'users.destroy',
            ]);
    });

    //admin and organizer routes
    Route::middleware('role:admin,organizer')->group(function () {
        Route::resource('events', EventController::class)
            ->except(['create', 'edit', 'update', 'show'])
            ->names([
                'store' => 'events.store',
                'destroy' => 'events.destroy',
                'index' => 'events.index',
            ]);

        Route::get('/events/{slug}', [EventController::class, 'show'])
            ->name('events.show');

        Route::post('/events/{event}', [EventController::class, 'update'])
            ->name('events.update');

        // Ticket Type Routes
        Route::prefix('ticket-types')->group(function () {
            Route::post('/', [TicketTypeController::class, 'store']);
            Route::put('/{id}', [TicketTypeController::class, 'update']);
            Route::delete('/{id}', [TicketTypeController::class, 'destroy']);
            Route::get('/{id}/available-quantity', [TicketTypeController::class, 'getAvailableQuantity']);
        });

        //checkin ticket route
        Route::get('/tickets/check-in/{ticket_code}', [TicketController::class, 'checkInTicket']);
    });

    //order route
    Route::post('/orders', [OrderController::class, 'createOrder']);

    //order index route
    Route::get('/orders', [OrderController::class, 'index']);

    //order show route
    Route::get('/orders/{order_number}', [OrderController::class, 'show']);

    //order cancel route
    Route::get('/orders/{order_number}/cancel', [OrderController::class, 'cancelOrder']);

    //payment route
    Route::post('/payments', [PaymentController::class, 'createPayment']);

    //create ticket route
    Route::get('/orders/{order_id}/tickets', [TicketController::class, 'createTicket']);
});

// Public routes
Route::get('/pub/categories', [CategoryController::class, 'index'])
    ->name('pub.categories.index');

Route::get('/pub/categories/{id}', [CategoryController::class, 'show'])
    ->name('pub.categories.show');

Route::get('/pub/events', [EventController::class, 'publicIndex'])
    ->name('pub.events.index');

Route::get('/pub/events/{slug}', [EventController::class, 'show'])
    ->name('pub.events.show');

//webhook midtrans
Route::middleware('guest')->post('/midtrans/callback', [MidtransController::class, 'callback'])
    ->name('midtrans.callback');
