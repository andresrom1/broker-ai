<?php

use App\Http\Controllers\KillController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Quotes\QuoteController;
use App\Http\Controllers\Quotes\QuoteAlternativeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('quote-form');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::view('/cotizar', 'quote-form')->middleware('auth');

Route::get('/admin/quotes', [QuoteController::class, 'index'])->name('quotes.index');
Route::get('/admin/quotes/{id}', [QuoteController::class, 'show'])->name('quotes.show');
Route::post('/admin/quotes/{id}', [QuoteAlternativeController::class, 'store'])->name('quotes.alternatives.store');

Route::get('kill', [KillController::class,'show'])->name('kill.show');
Route::post('/kill/handle', [KillController::class, 'handle'])->name('kill.handle');


require __DIR__.'/auth.php';
