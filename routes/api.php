<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Auth-----------------------------------------

Route::controller(AuthController::class)->group(function() {

    Route::get('/Home', 'home');

    Route::post('/Auth/checkMobile', 'authCheckMobile'); //true --> validate

    Route::post('/Auth/loginOtp', 'authLoginOtp'); //true

    Route::post('/Auth/resendOtp', 'authResendOtp'); //true

    Route::get('/Auth/logout', 'authLogout')->middleware('auth:sanctum'); //true

    Route::post('/Register', 'register')->middleware('auth:sanctum'); //true

    Route::post('/Register/verifyOtp', 'registerVerifyOtp'); //?

});

Route::controller(PostController::class)->group(function() {

    Route::post('/Post/create', 'postCreate')->middleware('auth:sanctum'); //true

    Route::get('/Post/{post_id}', 'post')->middleware('auth:sanctum'); //true

    Route::post('/Post/edit/{post_id}', 'postEdit')->middleware('auth:sanctum'); //true

    Route::delete('/Post/delete/{post_id}', 'postDelete')->middleware('auth:sanctum');

    Route::post('/Post/like/{post_id}', 'postLike')->middleware('auth:sanctum');

});

Route::controller(ProfileController::class)->group(function() {

    Route::get('/Profile/show', 'profileShow')->middleware('auth:sanctum');

});
