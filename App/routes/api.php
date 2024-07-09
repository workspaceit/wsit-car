<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*** Auth ***/
Route::namespace('Api')->prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/login', 'ApiLoginController@login')->name('login');
    Route::post('/logout', 'AuthApiController@logout')->name('logout');
    Route::post('/reset', 'AuthApiController@sendResetMail')->name('reset');
});
/*** Auth END ***/

/*** API V1 ***/

Route::namespace('Api\V1')->prefix('v1')->name('api.v1.')->group(function () {
    // Issue
    Route::post('all-issues', 'IssueApiController@index')->name('issues.index');
    Route::delete('issues/{issue}/photos', 'IssueApiController@photoDestroy')->name('issues.photos.destroy');
    Route::apiResource('issues', 'IssueApiController', [
        'except' => ['index']
    ]);
});

/*** API V1 END ***/
