<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

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
Route::get('/migrate-run-once', function () {
    Artisan::call('migrate');
    return 'Migrations have been run.';
});

Route::get('/storage-link', function () {
    Artisan::call('storage:link');
    return 'Storage link has been created.';
});

Route::get('/cron-run-once', function () {
    Artisan::call('messages:expire');
    return 'Cron have been run.';
});

Route::group(['middleware' => 'auth'], function ()
{
    // user
    Route::middleware('is_user')
    ->group(function(){
        Route::get('/profile', 'HomeController@viewProfile')->name('profile.view');
        Route::post('/profile/update', 'HomeController@updateProfile')->name('profile.update');
        Route::match(['get', 'post'], '/', 'HomeController@index')->name('home');
        Route::post('/messages', 'MessageController@store')->name('messages.store');
        Route::match(['get', 'post'], '/messages/{token}', 'MessageController@delete')->name('message.delete');

        Route::post('/image/store', 'ImageController@store')->name('image.store');
        Route::get('/image_action/{token}', 'ImageController@imageAction')->name('image.action');
        Route::get('/image/index', 'ImageController@list')->name('image.list');
        Route::post('/image/delete','ImageController@delete')->name('image.delete');
        Route::post('/image/download','ImageController@download')->name('image.download');


        Route::get('/fetch-data/view', 'MessageController@fetchData')->name('message.fetchData');
        Route::get('/{token}', 'MessageController@messageRead')->name('message.read1');
        Route::match(['get', 'post'], '/reply/message', 'MessageController@reply')->name('messages.reply');
        Route::post('delete/message', 'MessageController@deleteMessage')->name('message.delete');
        Route::match(['get', 'post'], '/chat/{token}', 'MessageController@deleteChat')->name('chat.delete');
        Route::post('extends-validity', 'MessageController@extendsValidity')->name('chat.extends-validity');


        // my notes notes
        Route::get('/notes/index', 'NotesController@list')->name('notes.list');
        Route::post('/notes/add', 'NotesController@add')->name('notes.add');
        Route::post('/detail','NotesController@detail')->name('notes.detail');
        Route::post('/notes/delete', 'NotesController@delete')->name('notes.delete');
        Route::post('/notes/pin', 'NotesController@pin')->name('notes.pin');
    });

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
        Route::post('/conversation/delete','MessageController@conversationDelete')->name('conversation.delete');
        Route::post('/delete-multiple-message', 'MessageController@deleteMultipleMessages')->name('multiple-message.delete');

        //user module
        Route::get('/user/list','UserController@userlist')->name('user.list');
        Route::get('/user/view','UserController@userview')->name('user.view');
        // Route::get('/user/edit','UserController@useredit')->name('user.edit');
        Route::get('/user/detail','UserController@userdetail')->name('user.detail');
        Route::post('/user/update','UserController@userupdate')->name('user.update');
        Route::post('/user/delete','UserController@userdelete')->name('user.delete');
        Route::post('/approve_user', 'UserController@approve_user')->name('user.approve_user');
        Route::post('/delete-multiple-users', 'UserController@deleteMultipleUsers')->name('multiple-user.delete');

        //Note module
        Route::get('/note/list','NoteController@notelist')->name('note.list');
        Route::post('/note/delete','NoteController@notedelete')->name('note.delete');
        Route::post('/delete-multiple-notes', 'NoteController@deleteMultipleNotes')->name('multiple-notes.delete');
    });
});


