<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'version' => '1.0.0',
        'documentation' => '/api/v1',
    ]);
});

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
