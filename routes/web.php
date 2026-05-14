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

Route::get('/seed-run-once', function () {
    Artisan::call('db:seed');
    return 'Seeder has been run.';
});

// Route::group(['middleware' => 'auth'], function ()
Route::group(['middleware' => ['auth', 'is_blocked']], function () {
    // user
    Route::middleware('is_user')
        ->group(function () {
            Route::get('/profile', 'HomeController@viewProfile')->name('profile.view');
            Route::post('/profile/update', 'HomeController@updateProfile')->name('profile.update');
            Route::match(['get', 'post'], '/', 'HomeController@index')->name('home');
            Route::post('/messages', 'MessageController@store')->name('messages.store');
            Route::match(['get', 'post'], '/messages/{token}', 'MessageController@delete')->name('message.delete');

            Route::post('/image/store', 'ImageController@store')->name('image.store');
            Route::get('/image_action/{token}', 'ImageController@imageAction')->name('image.action');
            Route::get('/image/index', 'ImageController@list')->name('image.list');
            Route::post('/image/delete', 'ImageController@delete')->name('image.delete');
            Route::post('/image/download', 'ImageController@download')->name('image.download');
            Route::post('/image/view', 'ImageController@viewFile')->name('image.view');
            Route::post('/delete-multiple-images', 'ImageController@deleteMultipleImages')->name('multiple-image.delete');

            Route::get('/fetch-data/view', 'MessageController@fetchData')->name('message.fetchData');
            Route::prefix('invite-user')->group(function () {
                Route::get('/get', 'MessageController@inviteUserGet')->name('invite.user.get');
                Route::post('/store', 'MessageController@inviteUserStore')->name('invite.user.store');
                Route::get('/suggestable-users', 'MessageController@getSuggestableUsers')->name('invite.suggestable.users');
            });

            Route::match(['get', 'post'], '/reply/message', 'MessageController@reply')->name('messages.reply');
            Route::post('delete/message', 'MessageController@deleteMessage')->name('message.delete');
            Route::post('pin/message', 'MessageController@pinMessage')->name('message.pin');
            Route::match(['get', 'post'], '/chat/{token}', 'MessageController@deleteChat')->name('chat.delete');
            Route::post('extends-validity', 'MessageController@extendsValidity')->name('chat.extends-validity');
            Route::get('/{token}', 'MessageController@messageRead')->name('message.read1');


            // my notes notes
            Route::get('/notes/index', 'NotesController@list')->name('notes.list');
            Route::post('/notes/add', 'NotesController@add')->name('notes.add');
            Route::post('/detail', 'NotesController@detail')->name('notes.detail');
            Route::post('/notes/delete', 'NotesController@delete')->name('notes.delete');
            Route::post('/notes/pin', 'NotesController@pin')->name('notes.pin');

            // delivery messages
            Route::get('/delivery-message/index', 'DeliveryController@list')->name('delivery.list');
            Route::post('/delivery-message/mark-read', 'DeliveryController@markRead')->name('delivery.markRead');

            // notifications
            Route::get('/notifications/index', 'DeliveryController@notificationList')->name('notifications.list');
            Route::post('/notifications/mark-read', 'DeliveryController@markRead')->name('notifications.markRead');
        });

    //Admin
    Route::namespace('Admin')
        ->middleware('is_admin')
        ->as('admin.')
        ->prefix('admin')
        ->group(function () {
            Route::get('/index', 'AdminController@index')->name('home');
            Route::get('/profile', 'AdminController@profile')->name('profile');
            Route::post('/profile/update', 'AdminController@profileupdate')->name('profile.update');

            //message module
            Route::get('/message', 'MessageController@message')->name('message');
            Route::get('/view-message', 'MessageController@viewChat')->name('view_chat');
            Route::post('/conversation/delete', 'MessageController@conversationDelete')->name('conversation.delete');
            Route::post('/delete-multiple-message', 'MessageController@deleteMultipleMessages')->name('multiple-message.delete');

            //user module
            Route::get('/user/list', 'UserController@userlist')->name('user.list');
            Route::get('/user/view', 'UserController@userview')->name('user.view');
            // Route::get('/user/edit','UserController@useredit')->name('user.edit');
            Route::get('/user/detail', 'UserController@userdetail')->name('user.detail');
            Route::post('/user/update', 'UserController@userupdate')->name('user.update');
            Route::post('/user/delete', 'UserController@userdelete')->name('user.delete');
            Route::post('/approve_user', 'UserController@approve_user')->name('user.approve_user');
            Route::post('/delete-multiple-users', 'UserController@deleteMultipleUsers')->name('multiple-user.delete');
            Route::post('/user-status-update', 'UserController@userStatusUpdate')->name('user-status-update');
            Route::post('/user-suggestable-update', 'UserController@userSuggestableUpdate')->name('user-suggestable-update');
            Route::post('/user-role-update', 'UserController@userRoleUpdate')->name('user-role-update');

            //Note module
            Route::get('/note/list', 'NoteController@notelist')->name('note.list');
            Route::post('/note/delete', 'NoteController@notedelete')->name('note.delete');
            Route::post('/delete-multiple-notes', 'NoteController@deleteMultipleNotes')->name('multiple-notes.delete');

            //Setting module
            Route::get('/setting/register', 'SettingController@registerSetting')->name('setting.register');
            Route::post('/setting/update', 'SettingController@update')->name('setting.update');

            //Delivery module
            Route::get('/delivery/list', 'DeliveryController@list')->name('delivery.list');
            Route::get('/delivery/details', 'DeliveryController@details')->name('delivery.details');
            Route::post('/delivery/add-update', 'DeliveryController@addupdate')->name('delivery.addupdate');
            Route::post('/delivery/delete', 'DeliveryController@delete')->name('delivery.delete');
            Route::get('/delivery/get-users', 'DeliveryController@getUsers')->name('delivery.users.list');
            Route::post('/delivery/image/delete', 'DeliveryController@deleteImage')->name('delivery.image.delete');
        });
});
