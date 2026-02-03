<?php

use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/receipts/{ticket}/print', [ReceiptController::class, 'show'])
    ->name('receipts.print')
    ->middleware('auth');
