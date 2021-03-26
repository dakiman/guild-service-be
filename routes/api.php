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

Route::get('/guild/{region}/{realm}/{guild}', 'GuildController@guild');
Route::get('/guild/popular', 'GuildController@popular');

Route::get('/character/{region}/{realm}/{characterName}', 'CharacterController@character');
Route::get('/character/popular', 'CharacterController@popular');
Route::patch('/character/{character}/recruitment', 'CharacterController@toggleRecruitment')->middleware('auth');


Route::get('/user','Auth\AuthController@user')->middleware('auth');
Route::post('/register', 'Auth\AuthController@register');
Route::post('/login', 'Auth\AuthController@login');
Route::post('/logout', 'Auth\AuthController@logout');

Route::post('/password/forgot', 'Auth\ForgotPasswordController')->name('password.forget');
Route::post('/password/reset', 'Auth\ResetPasswordController@reset')->name('password.reset');

Route::post('/{region}/blizzard-oauth', 'BlizzardController@code')->middleware('auth');

//Route::get('/test', function () {
//    dd(App\Models\Character::where('blizzard_data.basic.level', '>', 45)->get()->count());
//});

