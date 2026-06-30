<?php

use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

// Route::redirect (not a closure) so the route table stays cacheable in production.
Route::redirect('/', '/admin');

/*
 * Public short-link redirect. Declared last and constrained to a 6-character
 * alphanumeric code so it never shadows the Filament panel (/admin), assets,
 * or other application routes.
 */
Route::get('/{shortCode}', RedirectController::class)
    ->where('shortCode', '[A-Za-z0-9]{6}')
    ->name('short-link.redirect');
