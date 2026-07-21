<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\BookController;
use App\Http\Controllers\StudentController;

use App\Http\Controllers\FineController;

Route::get('/', function () {
    return redirect()->route('books.index');
});

Route::resource('books', BookController::class);
Route::resource('students', StudentController::class);
Route::resource('fines', FineController::class);

Route::post('/books/{book}/checkout', [BookController::class, 'checkout'])->name('books.checkout');
Route::post('/books/checkout/custom', [BookController::class, 'checkoutGeneric'])->name('students.checkout.custom');
Route::post('/books/{book}/checkin', [BookController::class, 'checkin'])->name('books.checkin');
