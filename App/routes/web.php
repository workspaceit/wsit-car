<?php

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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/logout', function(){
    return redirect()->route('home');
});
Auth::routes();
Route::get('/login/{provider}','Auth\LoginController@redirectToSocial')->where('provider', 'google|facebook')->name('social.login');
Route::get('/login/{provider}/callback','Auth\LoginController@handleSocialCallback')->where('provider', 'google|facebook');
Route::post('individuals/cars/updateImages', 'Auth\RegisterController@updateImages')->name('individuals.cars.updateImages');
Route::post('individuals/cars/images/destroy', 'Auth\RegisterController@destroyImage')->name('individuals.cars.images.destroy');

Route::prefix('passwords')->name('passwords.')->group(function (){
    Route::post('/confirm', 'Auth\Admin\PasswordConfirmController@confirm')->name('confirm');
    Route::get('/confirm', 'Auth\Admin\PasswordConfirmController@showConfirmForm')->name('showConfirmForm');
});
Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::middleware('auth:web')->name('dealers.')->namespace('Panels\Dealer')->group(function () {
        Route::get('/tasksfetch', 'DashboardController@fetchPendingTaskRecords')->name('tasks.fetch');
    });
});
