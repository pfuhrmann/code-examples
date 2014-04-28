<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

// Home
Route::get('/', 'LoginController@showLogin');
Route::post('/', 'LoginController@login');

// Route group for API versioning
Route::group(array('before' => 'auth.api', 'prefix' => 'v1/'), function()
{
    // Campaigns
    Route::resource('campaigns', 'CampaignController');
    Route::get('campaigns/{lat}/{long}', 'CampaignController@showByGeolocation');

    // Advertisers
    Route::resource('advertisers', 'AdvertiserController');
    
    // Users
    Route::resource('users', 'UserController');
    Route::get('users/checkmail/{email}', 'UserController@checkMail');
    Route::post('users/login', 'UserController@login');
    Route::post('users/checkpin', 'UserController@checkPin');

    // Options
    Route::resource('options', 'OptionsController');
    Route::get('users/{id}/options', 'OptionsController@getByUser');
    Route::post('options/{id}/redeem', 'OptionsController@redeem');

    // Authorizations
    Route::resource('authorizations', 'AuthorizationController');

    // Channels
    Route::resource('channels', 'ChannelController');

    // Devices
    Route::resource('devices', 'DeviceController'); 
});