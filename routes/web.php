<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Pages\ProjectDetails;
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

Route::get('/welcome', function () {
    return view('welcome');
});
// Route::get('/admin/projects/{record}', ProjectDetails::class)->name('filament.pages.project_details');

Route::get('/storage/download-all', [App\Http\Controllers\StorageDownloadController::class, 'downloadAll'])
    ->name('storage.download-all')
    ->middleware(['auth', 'verified']);
