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
    Route::get('/profile', 'HomeController@viewProfile')->name('profile.view');
    Route::post('/profile/update', 'HomeController@updateProfile')->name('profile.update');
    Route::match(['get', 'post'], '/', 'HomeController@index')->name('home');
    Route::post('/messages', 'MessageController@store')->name('messages.store');
    Route::match(['get', 'post'], '/messages/{token}', 'MessageController@delete')->name('message.delete');        
    Route::get('/{token}', 'MessageController@messageConfirm')->name('message.confirm');    
    Route::get('/read/{token}', 'MessageController@messageRead')->name('message.read');      
    Route::match(['get', 'post'], '/reply/message', 'MessageController@reply')->name('messages.reply');
    Route::match(['get', 'post'], '/chat/{token}', 'MessageController@deleteChat')->name('chat.delete');

    //Admin
    Route::namespace('Admin')
    ->middleware('is_admin')
    ->as('admin.')
    ->prefix('admin')
    ->group(function(){    
        Route::get('/index', 'AdminController@index')->name('home'); 
        Route::get('/profile', 'AdminController@profile')->name('profile');
        Route::post('/profile/update','AdminController@profileupdate')->name('profile.update');
        
        //message module
        Route::get('/message','MessageController@message')->name('message');   
        Route::get('/view-message', 'MessageController@viewChat')->name('view_chat'); 

        //user module
        Route::get('/user/list','UserController@userlist')->name('user.list');   
        Route::get('/user/view','UserController@userview')->name('user.view');   
        // Route::get('/user/edit','UserController@useredit')->name('user.edit');  
        Route::get('/user/detail','UserController@userdetail')->name('user.detail');
        Route::post('/user/update','UserController@userupdate')->name('user.update'); 
        Route::post('/user/delete','UserController@userdelete')->name('user.delete'); 
    });
});


