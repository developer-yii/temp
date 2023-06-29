<?php

use Illuminate\Support\Facades\Route;

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

Auth::routes();

Route::group(['middleware' => 'auth'], function () 
{
        
        Route::get('/profile', [App\Http\Controllers\HomeController::class, 'viewProfile'])->name('profile.view');
        Route::post('/profile/update', [App\Http\Controllers\HomeController::class, 'updateProfile'])->name('profile.update');
        Route::match(['get', 'post'], '/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
        Route::post('/messages', [App\Http\Controllers\MessageController::class, 'store'])->name('messages.store');
        Route::match(['get', 'post'], '/messages/{token}', [App\Http\Controllers\MessageController::class, 'delete'])->name('message.delete');        
        Route::get('/{token}', [App\Http\Controllers\MessageController::class, 'messageConfirm'])->name('message.confirm');    
        Route::get('/read/{token}', [App\Http\Controllers\MessageController::class, 'messageRead'])->name('message.read');      
        Route::match(['get', 'post'], '/reply/message', [App\Http\Controllers\MessageController::class, 'reply'])->name('messages.reply');
        Route::match(['get', 'post'], '/chat/{token}', [App\Http\Controllers\MessageController::class, 'deleteChat'])->name('chat.delete');
});
