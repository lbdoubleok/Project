<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\homecontroller;
use App\Http\Controllers\AdminController;

Route::get('/home', [homecontroller::class ,'home'])->name('home');

Route::redirect('/', '/admin');
 
Route::prefix('admin')->name('admin.')->controller(AdminController::class)->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', 'showLogin')->name('login');
        Route::post('login', 'login')->middleware('throttle:10,1')->name('login.attempt');
    });
 
    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', 'logout')->name('logout');
        Route::get('/', 'dashboards')->name('dashboards');
 
        Route::get('rooms', 'roomsIndex')->name('rooms.index');
        Route::get('rooms/create', 'roomCreate')->name('rooms.create');
        Route::post('rooms', 'roomStore')->name('rooms.store');
        Route::get('rooms/{room}/edit', 'roomEdit')->name('rooms.edit');
        Route::put('rooms/{room}', 'roomUpdate')->name('rooms.update');
        Route::patch('rooms/{room}/toggle', 'roomToggle')->name('rooms.toggle');
        Route::put('room-types/{roomType}', 'roomTypeUpdate')->name('room-types.update');
 
        Route::get('bookings', 'bookingsIndex')->name('bookings.index');
        Route::get('bookings/{booking}', 'bookingShow')->name('bookings.show');
        Route::patch('bookings/{booking}/status', 'bookingUpdateStatus')->name('bookings.status');
        Route::post('bookings/{booking}/cancel', 'bookingCancel')->name('bookings.cancel');
 
        Route::get('payments', 'paymentsIndex')->name('payments.index');
        Route::post('payments/{payment}/verify', 'paymentVerify')->name('payments.verify');
        Route::post('payments/{payment}/reject', 'paymentReject')->name('payments.reject');
 
        Route::get('customers', 'customersIndex')->name('customers.index');
        Route::get('customers/{customer}', 'customerShow')->name('customers.show');
        Route::get('cats', 'catsIndex')->name('cats.index');
        Route::get('cameras', 'camerasIndex')->name('cameras.index');
        Route::post('cameras', 'cameraStore')->name('cameras.store');
        Route::delete('cameras/{camera}', 'cameraDestroy')->name('cameras.destroy');
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
