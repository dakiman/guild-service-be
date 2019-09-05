<?php

use Illuminate\Http\Request;

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

Route::middleware('auth')->get('/user', function (Request $request) {
    return response()->json([
       'user' => \Auth::user()
    ], 200);
});

Route::post('/register', 'AuthController@register');
Route::post('/login', 'AuthController@login');
Route::post('/logout', 'AuthController@logout');

Route::post('/password/forgot', 'Auth\ForgotPasswordController')->name('password.forget');
Route::post('/password/reset', 'Auth\ResetPasswordController@reset')->name('password.reset');
