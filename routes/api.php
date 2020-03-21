<?php

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

Route::get('/guild/{realm}/{guild}', 'GuildController@guild');
Route::get('/character/{realm}/{character}', 'CharacterController@character');


Route::get('/user','Auth\AuthController@user')->middleware('auth');
Route::post('/register', 'Auth\AuthController@register');
Route::post('/login', 'Auth\AuthController@login');
Route::post('/logout', 'Auth\AuthController@logout');

Route::post('/password/forgot', 'Auth\ForgotPasswordController')->name('password.forget');
Route::post('/password/reset', 'Auth\ResetPasswordController@reset')->name('password.reset');

Route::post('/blizzard-oauth', 'BlizzardController@code');
