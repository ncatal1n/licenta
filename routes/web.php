<?php

use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::view('dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Route::view("/", 'home')->name("home");

Route::view('upload', 'upload')
    ->middleware(['auth'])
    ->name('ui.upload');

Route::get('view/{uqid}', [ImageController::class, 'view'])->name("ui.image.view")->middleware(['auth']);

Route::get('view/render/{uqid}', [ImageController::class, 'render'])->name("image.render");



require __DIR__ . '/auth.php';
