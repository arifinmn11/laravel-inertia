<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Cms\ProfilesController;
use App\Http\Controllers\Cms\DashboardController;
use App\Http\Controllers\Cms\PermissionsController;
use App\Http\Controllers\Cms\RolesController;
use App\Http\Controllers\Cms\UsersController;
use App\Http\Libraries\ManaCms;
use Illuminate\Support\Facades\Route;

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

// Auth

Route::get('login', [AuthenticatedSessionController::class, 'create'])
    ->name('login')
    ->middleware('guest');

Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->name('login.store')
    ->middleware('guest');

Route::delete('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');



Route::group(['middleware' => 'auth'], function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    //Permissions
    Route::get('permissions', [PermissionsController::class, 'index'])->name('permissions.index');
    Route::get('permissions/{permission}/edit', [PermissionsController::class, 'edit'])->name('permissions.edit');
    Route::put('permissions/{permission}', [PermissionsController::class, 'update'])->name('permissions.update');

    //Profile
    Route::get('profiles', [ProfilesController::class, 'index'])->name('profiles');
    Route::put('profiles', [ProfilesController::class, 'update'])->name('profiles.update');

    //Roles
    Route::get('roles', [RolesController::class, 'index'])->name('roles');
    Route::get('roles/create', [RolesController::class, 'create'])->name('roles.create');
    Route::post('roles', [RolesController::class, 'store'])->name('roles.store');
    Route::get('roles/{role}/edit', [RolesController::class, 'edit'])->name('roles.edit');
    Route::put('roles/{role}', [RolesController::class, 'update'])->name('roles.update');
    Route::delete('roles/{role}', [RolesController::class, 'destroy'])->name('roles.destroy');

    // Users
    Route::get('users', [UsersController::class, 'index'])->name('users');
    Route::get('users/create', [UsersController::class, 'create'])->name('users.create');
    Route::post('users', [UsersController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
    Route::put('users/{user}/restore', [UsersController::class, 'restore'])->name('users.restore');

    //Menu
    Route::get('menu', function () {
        return response()->json(ManaCms::listMenu());
    });

    Route::get('menu_version', function () {
        return response()->json(['version' => 1]);
    });
});
