<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BucketListController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\CoupleTimelineController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ImportantDateController;
use App\Http\Controllers\LoveLetterController;
use App\Http\Controllers\MemoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SharedEventController;
use App\Http\Controllers\SharedMemoryController;
use App\Http\Controllers\TimelineController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Pages
|--------------------------------------------------------------------------
*/

Route::view('/', 'home')->name('home');
Route::view('/about', 'about');
Route::view('/contact', 'contact');
Route::view('/features', 'features');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated Features
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Memories
    Route::get('/memories/{memory}/image', [MemoryController::class, 'image'])->name('memories.image');
    Route::resource('memories', MemoryController::class);

    // Profile
    Route::get('/users/{user}/avatar', [ProfileController::class, 'avatar'])->name('user.avatar');

    // Favorites (AJAX toggle + collection page)
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/memories/{memory}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    // Photo Gallery
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::get('/gallery/{memory}/download', [GalleryController::class, 'download'])->name('gallery.download');

    // Love Letters
    Route::resource('letters', LoveLetterController::class)->parameters(['letters' => 'loveLetter']);
    Route::post('/letters/{loveLetter}/pin', [LoveLetterController::class, 'togglePin'])->name('letters.toggle-pin');

    // Shared Memories
    Route::get('/shared-memories', [SharedMemoryController::class, 'index'])->name('shared-memories.index');
    Route::get('/memories/{memory}/share', [SharedMemoryController::class, 'create'])->name('memories.share');
    Route::post('/memories/{memory}/share', [SharedMemoryController::class, 'store'])->name('shared-memories.store');
    Route::delete('/shared-memories/{sharedMemory}', [SharedMemoryController::class, 'destroy'])->name('shared-memories.destroy');

    // Timeline
    Route::get('/timeline', [TimelineController::class, 'index'])->name('timeline.index');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/date', [CalendarController::class, 'onDate'])->name('calendar.date');

    // Shared Events (couple calendar)
    Route::get('/calendar/events/create', [SharedEventController::class, 'create'])->name('events.create');
    Route::post('/calendar/events', [SharedEventController::class, 'store'])->name('events.store');
    Route::get('/calendar/events/{sharedEvent}', [SharedEventController::class, 'show'])->name('events.show');
    Route::get('/calendar/events/{sharedEvent}/edit', [SharedEventController::class, 'edit'])->name('events.edit');
    Route::put('/calendar/events/{sharedEvent}', [SharedEventController::class, 'update'])->name('events.update');
    Route::delete('/calendar/events/{sharedEvent}', [SharedEventController::class, 'destroy'])->name('events.destroy');

    // Important Dates
    Route::get('/important-dates', [ImportantDateController::class, 'index'])->name('important-dates.index');
    Route::post('/important-dates', [ImportantDateController::class, 'store'])->name('important-dates.store');
    Route::get('/important-dates/{importantDate}/edit', [ImportantDateController::class, 'edit'])->name('important-dates.edit');
    Route::put('/important-dates/{importantDate}', [ImportantDateController::class, 'update'])->name('important-dates.update');
    Route::delete('/important-dates/{importantDate}', [ImportantDateController::class, 'destroy'])->name('important-dates.destroy');

    // Couple Timeline
    Route::get('/couple-timeline', [CoupleTimelineController::class, 'index'])->name('couple-timeline.index');

    // Shared Playlist
    Route::get('/playlists', [PlaylistController::class, 'index'])->name('playlists.index');
    Route::post('/playlists', [PlaylistController::class, 'store'])->name('playlists.store');
    Route::get('/playlists/{sharedPlaylist}/edit', [PlaylistController::class, 'edit'])->name('playlists.edit');
    Route::put('/playlists/{sharedPlaylist}', [PlaylistController::class, 'update'])->name('playlists.update');
    Route::delete('/playlists/{sharedPlaylist}', [PlaylistController::class, 'destroy'])->name('playlists.destroy');
    Route::post('/playlists/{sharedPlaylist}/tracks', [PlaylistController::class, 'addTrack'])->name('playlists.tracks.store');
    Route::delete('/playlists/{sharedPlaylist}/tracks/{playlistTrack}', [PlaylistController::class, 'removeTrack'])->name('playlists.tracks.destroy');

    // Bucket List
    Route::get('/bucket-list', [BucketListController::class, 'index'])->name('bucket-list.index');
    Route::post('/bucket-list', [BucketListController::class, 'store'])->name('bucket-list.store');
    Route::patch('/bucket-list/{bucketListItem}/toggle', [BucketListController::class, 'toggle'])->name('bucket-list.toggle');
    Route::delete('/bucket-list/{bucketListItem}', [BucketListController::class, 'destroy'])->name('bucket-list.destroy');

    // Global Search
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/instant', [SearchController::class, 'instant'])->name('search.instant')->middleware('throttle:30,1');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar'])->name('profile.remove-avatar');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::delete('/settings/account', [SettingsController::class, 'deleteAccount'])->name('settings.delete-account');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    // Connections
    Route::get('/connections', [ConnectionController::class, 'index'])->name('connections.index');
    Route::post('/connections', [ConnectionController::class, 'store'])->name('connections.store')->middleware('throttle:10,1');
    Route::patch('/connections/{connection}/accept', [ConnectionController::class, 'accept'])->name('connections.accept');
    Route::patch('/connections/{connection}/reject', [ConnectionController::class, 'reject'])->name('connections.reject');
    Route::delete('/connections/{connection}', [ConnectionController::class, 'destroy'])->name('connections.destroy');

});
