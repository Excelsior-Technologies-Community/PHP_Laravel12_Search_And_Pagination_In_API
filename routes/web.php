<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyWebController;


Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| GET → show pages (index, create, edit)  
| POST → store & update  
| DELETE → delete
|
*/

// Redirect home to properties list


// List all properties
Route::get('properties', [PropertyWebController::class, 'index'])->name('properties.index');

// Show create property form
Route::get('properties/create', [PropertyWebController::class, 'create'])->name('properties.create');

// Store new property
Route::post('properties/store', [PropertyWebController::class, 'store'])->name('properties.store');

// Show edit property form
Route::get('properties/edit/{id}', [PropertyWebController::class, 'edit'])->name('properties.edit');

// Update property
Route::post('properties/update/{id}', [PropertyWebController::class, 'update'])->name('properties.update');

// Delete property
Route::delete('properties/delete/{id}', [PropertyWebController::class, 'destroy'])->name('properties.destroy');
