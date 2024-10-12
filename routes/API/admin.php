<?php

use App\Http\Controllers\API\Admin\AuthController;
use App\Http\Controllers\API\Admin\ImageController;
use App\Http\Controllers\API\Admin\PermissionController;
use App\Http\Controllers\API\Admin\RoleController;
use App\Http\Controllers\API\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin/', 'namespace' => 'App\Http\Controllers\API\Admin'], function () {

    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::middleware(['admin'])->group(function () {

            Route::get('logout', [AuthController::class, 'logout'])->name('logout');

            // Users (admin)
            Route::get('users', [UserController::class, 'index']);
            Route::post('users', [UserController::class, 'store']);
            Route::get('users/{id}', [UserController::class, 'show']);
            Route::put('users/{id}', [UserController::class, 'update']);
            Route::delete('users/{id}', [UserController::class, 'destroy']);
            Route::post('/users/change_password/{id}', [UserController::class, 'changePassword']);

            // Role
            Route::get('roles', [RoleController::class, 'index']);
            Route::post('roles', [RoleController::class, 'store']);
            Route::get('roles/{id}', [RoleController::class, 'show']);
            Route::put('roles/{id}', [RoleController::class, 'update']);
            Route::delete('roles/{id}', [RoleController::class, 'destroy']);

            // Permission
            Route::get('permissions',  [PermissionController::class, 'index']);
            Route::post('permissions', [PermissionController::class, 'store']);
            Route::get('permissions/{id}', [PermissionController::class, 'show']);
            Route::put('permissions/{id}', [PermissionController::class, 'update']);
            Route::delete('permissions/{id}', [PermissionController::class, 'destroy']);

            //image
            Route::post('/images/upload', [ImageController::class, 'singleUpload']);
            Route::delete('/images/{id}', [ImageController::class, 'delete']);
            Route::delete('/images', [ImageController::class, 'deleteImageFromS3']);
        });
    });
});
