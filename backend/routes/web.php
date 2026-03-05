<?php

use Illuminate\Support\Facades\Route;

// Optional: just leave web.php empty or keep middleware routes
// Only use API for your frontend

// Example: fallback route (optional)
Route::get('/{any}', function () {
    return response()->json(['message' => 'Backend running']);
})->where('any', '.*');