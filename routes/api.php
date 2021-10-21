<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TweetAllController;
use App\Http\Controllers\TweetController;
use App\Http\Controllers\UserFollowController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserTweetsController;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tweets', [TweetController::class, 'index'])->name('tweet.index');
    Route::get('/tweets_all', [TweetAllController::class, 'index'])->name('tweet.index.all');
    Route::get('/tweets/{tweet}', [TweetController::class, 'show'])->name('tweet.show');
    Route::post('/tweets', [TweetController::class, 'store'])->name('tweet.store');
    Route::delete('/tweets/{tweet}', [TweetController::class, 'destroy'])->name('tweet.delete');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/follow/{user}', [UserFollowController::class, 'store'])->name('user.follow');
    Route::post('/unfollow/{user}', [UserFollowController::class, 'destroy'])->name('user.unfollow');
    Route::get('/is_following/{user}', [UserFollowController::class, 'isFollowing'])->name('user.isFollowing');
});

Route::get('/users/{user}', [UserProfileController::class, 'show'])->name('user.profile.show');
Route::get('/users/{user}/tweets', [UserTweetsController::class, 'index'])->name('user.tweets.index');

Route::post('/login', [AuthController::class, 'store'])->name('login');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'destroy'])->name('logout');

Route::post('/register', [RegisterController::class, 'store'])->name('register');
