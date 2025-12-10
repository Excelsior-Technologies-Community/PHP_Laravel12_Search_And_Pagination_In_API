<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PropertyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes using only GET and POST methods.
|
*/

// Get all properties (with search + pagination)
Route::get('properties', [PropertyController::class, 'index']);

// Get single property by ID
Route::get('properties/{id}', [PropertyController::class, 'show']);

// Create a new property
Route::post('properties/create', [PropertyController::class, 'store']);

// Update property by ID (via POST)
Route::post('properties/update/{id}', [PropertyController::class, 'update']);

// Delete property by ID (via POST)
Route::post('properties/delete/{id}', [PropertyController::class, 'destroy']);

// Optional test route
Route::get('test', function () {
    return response()->json(['message' => 'API is working']);
});
