<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/tweets_all', function () {
    return Tweet::with('user:id,name,username,avatar')->latest()->paginate(10);
});

Route::middleware('auth:sanctum')->get('/tweets', function () {
    $followers = auth()->user()->follows->pluck('id');

    return Tweet::with('user:id,name,username,avatar')->whereIn('user_id', $followers)->latest()->paginate(10);
});

Route::get('/tweets/{tweet}', function (Tweet $tweet) {
    return $tweet->load('user:id,name,username,avatar');
});

Route::middleware('auth:sanctum')->post('/tweets', function (Request $request) {
    $request->validate([
        'body' => 'required',
    ]);

    return Tweet::create([
        'user_id' => auth()->id(),
        'body' => $request->body,
    ]);
});

Route::middleware('auth:sanctum')->delete('/tweets/{tweet}', function (Tweet $tweet) {
    abort_if($tweet->user->id !== auth()->id(), 403);

    return response()->json($tweet->delete(), 200);
});

Route::get('/users/{user}', function (User $user) {
    return $user->only(
        'id',
        'name',
        'username',
        'avatar',
        'profile',
        'location',
        'link',
        'linkText',
        'created_at'
    );
});

Route::get('/users/{user}/tweets', function (User $user) {
    return $user->tweets()->with('user:id,name,username,avatar')->latest()->paginate(10);
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $token = $user->createToken($request->device_name)->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => $user->only('id', 'name', 'username', 'email', 'avatar'),
    ], 201);
});

Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json('Logged out', 200);
});

Route::post('/register', function (Request $request) {
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'username' => 'required|min:4|unique:users',
        'password' => 'required|min:6|confirmed',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'username' => $request->username,
        'password' => Hash::make($request->password),
    ]);

    $user->follows()->attach($user);

    return response()->json($user, 201);
});

Route::middleware('auth:sanctum')->post('/follow/{user}', function (User $user) {
    auth()->user()->follow($user);

    return response()->json('Followed', 201);
});

Route::middleware('auth:sanctum')->post('/unfollow/{user}', function (User $user) {
    auth()->user()->unfollow($user);

    return response()->json('Unfollowed', 201);
});

Route::middleware('auth:sanctum')->get('/is_following/{user}', function (User $user) {
    return response()->json(auth()->user()->isFollowing($user), 200);
});
