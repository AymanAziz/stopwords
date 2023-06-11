<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Route::get('/upload', 'UploadController@index');

Route::get('/upload', [UploadController::class, 'index'])->name('index');

//Route::post('/upload', 'UploadController@upload')->name('upload.file');
Route::post('/upload', [UploadController::class, 'upload'])->name('upload.file');
