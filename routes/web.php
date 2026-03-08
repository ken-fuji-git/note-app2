<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\DogController;
use App\Http\Controllers\JourneyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Day1で入力したルーティング先
// Route::middleware('auth')の外なので、未認証でもアクセス可能
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Day1で入力したルーティング先。
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // AI相談
    Route::post('/ai/ask', [AiController::class, 'ask'])->name('ai.ask');

    // おかげ犬
    Route::get('/dogs/create', [DogController::class, 'create'])->name('dogs.create');
    Route::post('/dogs', [DogController::class, 'store'])->name('dogs.store');
    Route::get('/journey/departure', [JourneyController::class, 'departure'])->name('journey.departure');
    Route::post('/journey/start', [JourneyController::class, 'start'])->name('journey.start');
    Route::get('/journey/{journey}', [JourneyController::class, 'show'])->name('journey.show');
    Route::get('/journey/{journey}/story', [JourneyController::class, 'story'])->name('journey.story');
    Route::get('/journey/{journey}/generate-story', [JourneyController::class, 'generateStory'])->name('journey.generate-story');
    Route::get('/journey/{journey}/share', [JourneyController::class, 'share'])->name('journey.share');
});

// ⬇️ ⚠️ /posts/create と衝突するため /posts/{post} は middleware グループの後に書く
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

require __DIR__ . '/auth.php';
