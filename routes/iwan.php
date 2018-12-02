<?php

use Afaneh262\Iwan\Events\Routing;
use Afaneh262\Iwan\Events\RoutingAdmin;
use Afaneh262\Iwan\Events\RoutingAdminAfter;
use Afaneh262\Iwan\Events\RoutingAfter;
use Afaneh262\Iwan\Models\DataType;

/*
|--------------------------------------------------------------------------
| Iwan Routes
|--------------------------------------------------------------------------
|
| This file is where you may override any of the routes that are included
| with Iwan.
|
*/

Route::group(['as' => 'iwan.'], function () {
    event(new Routing());

    $namespacePrefix = '\\' . config('iwan.controllers.namespace') . '\\';

    Route::get('login', ['uses' => $namespacePrefix . 'IwanAuthController@login', 'as' => 'login']);
    Route::post('login', ['uses' => $namespacePrefix . 'IwanAuthController@postLogin', 'as' => 'postlogin']);

    // Password Reset Routes...
    Route::get('password/reset', ['uses' => $namespacePrefix . 'IwanForgotPasswordController@showLinkRequestForm', 'as' => 'showLinkRequestForm']);
    Route::post('password/email', ['uses' => $namespacePrefix . 'IwanForgotPasswordController@sendResetLinkEmail', 'as' => 'sendResetLinkEmail']);
    Route::get('password/reset/{token}', ['uses' => $namespacePrefix . 'IwanResetPasswordController@showResetForm', 'as' => 'showResetForm']);
    Route::post('password/reset', ['uses' => $namespacePrefix . 'IwanResetPasswordController@reset', 'as' => 'reset']);


    Route::group(['middleware' => 'admin.user'], function () use ($namespacePrefix) {
        event(new RoutingAdmin());

        // Main Admin and Logout Route
        Route::get('/', ['uses' => $namespacePrefix . 'IwanController@index', 'as' => 'dashboard']);
        Route::post('logout', ['uses' => $namespacePrefix . 'IwanController@logout', 'as' => 'logout']);
        Route::post('upload', ['uses' => $namespacePrefix . 'IwanController@upload', 'as' => 'upload']);

        Route::get('profile', ['uses' => $namespacePrefix . 'IwanController@profile', 'as' => 'profile']);

        try {
            foreach (DataType::all() as $dataType) {
                $breadController = $dataType->controller
                    ? $dataType->controller
                    : $namespacePrefix . 'IwanBaseController';

                Route::get($dataType->slug . '/order', $breadController . '@order')->name($dataType->slug . '.order');
                Route::post($dataType->slug . '/order', $breadController . '@update_order')->name($dataType->slug . '.order');
                Route::resource($dataType->slug, $breadController);
            }
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException("Custom routes hasn't been configured because: " . $e->getMessage(), 1);
        } catch (\Exception $e) {
            // do nothing, might just be because table not yet migrated.
        }

        // Role Routes
        Route::resource('roles', $namespacePrefix . 'IwanRoleController');

        // Menu Routes
        Route::group([
            'as' => 'menus.',
            'prefix' => 'menus/{menu}',
        ], function () use ($namespacePrefix) {
            Route::get('builder', ['uses' => $namespacePrefix . 'IwanMenuController@builder', 'as' => 'builder']);
            Route::post('order', ['uses' => $namespacePrefix . 'IwanMenuController@order_item', 'as' => 'order']);

            Route::group([
                'as' => 'item.',
                'prefix' => 'item',
            ], function () use ($namespacePrefix) {
                Route::delete('{id}', ['uses' => $namespacePrefix . 'IwanMenuController@delete_menu', 'as' => 'destroy']);
                Route::post('/', ['uses' => $namespacePrefix . 'IwanMenuController@add_item', 'as' => 'add']);
                Route::put('/', ['uses' => $namespacePrefix . 'IwanMenuController@update_item', 'as' => 'update']);
            });
        });

        // Settings
        Route::group([
            'as' => 'settings.',
            'prefix' => 'settings',
        ], function () use ($namespacePrefix) {
            Route::get('/', ['uses' => $namespacePrefix . 'IwanSettingsController@index', 'as' => 'index']);
            Route::post('/', ['uses' => $namespacePrefix . 'IwanSettingsController@store', 'as' => 'store']);
            Route::put('/', ['uses' => $namespacePrefix . 'IwanSettingsController@update', 'as' => 'update']);
            Route::delete('{id}', ['uses' => $namespacePrefix . 'IwanSettingsController@delete', 'as' => 'delete']);
            Route::get('{id}/move_up', ['uses' => $namespacePrefix . 'IwanSettingsController@move_up', 'as' => 'move_up']);
            Route::get('{id}/move_down', ['uses' => $namespacePrefix . 'IwanSettingsController@move_down', 'as' => 'move_down']);
            Route::get('{id}/delete_value', ['uses' => $namespacePrefix . 'IwanSettingsController@delete_value', 'as' => 'delete_value']);
        });

        // Admin Media
        Route::group([
            'as' => 'media.',
            'prefix' => 'media',
        ], function () use ($namespacePrefix) {
            Route::get('/', ['uses' => $namespacePrefix . 'IwanMediaController@index', 'as' => 'index']);
            Route::post('files', ['uses' => $namespacePrefix . 'IwanMediaController@files', 'as' => 'files']);
            Route::get('files', ['uses' => $namespacePrefix . 'IwanMediaController@listFiles', 'as' => 'getFiles']);

            Route::post('new_folder', ['uses' => $namespacePrefix . 'IwanMediaController@new_folder', 'as' => 'new_folder']);
            Route::post('delete_file_folder', ['uses' => $namespacePrefix . 'IwanMediaController@delete_file_folder', 'as' => 'delete_file_folder']);
            Route::post('directories', ['uses' => $namespacePrefix . 'IwanMediaController@get_all_dirs', 'as' => 'get_all_dirs']);
            Route::post('move_file', ['uses' => $namespacePrefix . 'IwanMediaController@move_file', 'as' => 'move_file']);
            Route::post('rename_file', ['uses' => $namespacePrefix . 'IwanMediaController@rename_file', 'as' => 'rename_file']);
            Route::post('upload', ['uses' => $namespacePrefix . 'IwanMediaController@upload', 'as' => 'upload']);
            Route::post('upload2', ['uses' => $namespacePrefix . 'IwanMediaController@upload2', 'as' => 'upload2']);
            Route::post('remove', ['uses' => $namespacePrefix . 'IwanMediaController@remove', 'as' => 'remove']);
            Route::post('crop', ['uses' => $namespacePrefix . 'IwanMediaController@crop', 'as' => 'crop']);
        });

        // BREAD Routes
        Route::group([
            'as' => 'bread.',
            'prefix' => 'bread',
        ], function () use ($namespacePrefix) {
            Route::get('/', ['uses' => $namespacePrefix . 'IwanBreadController@index', 'as' => 'index']);
            Route::get('{table}/create', ['uses' => $namespacePrefix . 'IwanBreadController@create', 'as' => 'create']);
            Route::post('/', ['uses' => $namespacePrefix . 'IwanBreadController@store', 'as' => 'store']);
            Route::get('{table}/edit', ['uses' => $namespacePrefix . 'IwanBreadController@edit', 'as' => 'edit']);
            Route::put('{id}', ['uses' => $namespacePrefix . 'IwanBreadController@update', 'as' => 'update']);
            Route::delete('{id}', ['uses' => $namespacePrefix . 'IwanBreadController@destroy', 'as' => 'delete']);
            Route::post('relationship', ['uses' => $namespacePrefix . 'IwanBreadController@addRelationship', 'as' => 'relationship']);
            Route::get('delete_relationship/{id}', ['uses' => $namespacePrefix . 'IwanBreadController@deleteRelationship', 'as' => 'delete_relationship']);
        });

        // Database Routes
        Route::resource('database', $namespacePrefix . 'IwanDatabaseController');

        // Compass Routes
        Route::group([
            'as' => 'compass.',
            'prefix' => 'compass',
        ], function () use ($namespacePrefix) {
            Route::get('/', ['uses' => $namespacePrefix . 'IwanCompassController@index', 'as' => 'index']);
            Route::post('/', ['uses' => $namespacePrefix . 'IwanCompassController@index', 'as' => 'post']);
        });

        event(new RoutingAdminAfter());
    });
    event(new RoutingAfter());
});
