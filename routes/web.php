<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampanhaController;
use App\Http\Controllers\ConfigAccountController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use App\Models\ConfigAccount;
use App\v8Service;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware('auth')->name('welcome');

Route::prefix('auth')->group(function () {
    Route::get('/login',[AuthController::class,'index'])->name('login');
    Route::post('/login',[AuthController::class,'login'])->name('auth.login');
    Route::get('/logout',[AuthController::class,'logout'])->name('auth.logout');
});

Route::group(['prefix' => 'campanhas','middleware'=>'auth'], function () {
    Route::get('/', [CampanhaController::class, 'index'])->name('campanhas.index');
    Route::post('/new', [CampanhaController::class, 'store'])->name('campanhas.store');
    Route::get('/{campanha}/status/{tipo}', [CampanhaController::class, 'status'])->name('campanhas.status');
    Route::get('/{campanha}/download', [CampanhaController::class, 'download'])->name('campanhas.download');
});

Route::get('utils/downloadModelo', [CampanhaController::class, 'downloadModelo'])->name('campanhas.downloadModelo');

Route::group(['prefix' => 'account','middleware'=>'auth'], function () {
    Route::get('/', [ConfigAccountController::class, 'index'])->name('account.index');
    Route::post('/new', [ConfigAccountController::class, 'store'])->name('account.store');
    Route::post('/update', [ConfigAccountController::class, 'update'])->name('account.update');
    Route::get('/{account}/edit', [ConfigAccountController::class, 'show'])->name('account.edit');
    Route::post('/{account}/edit', [ConfigAccountController::class, 'edit'])->name('account.edit.post');
    Route::get('/{account}/delete', [ConfigAccountController::class, 'destroy'])->name('account.delete');
    Route::post('/{account}/contractlink', [ConfigAccountController::class, 'create_contractlink'])->name('account.create.contractlink');
});

Route::group(['prefix' => 'users','middleware'=>['auth',AdminMiddleware::class]], function () {
    Route::get('/', [UserController::class, 'index'])->name('users.index');
    Route::post('/new', [UserController::class, 'store'])->name('users.store');
    Route::get('/{user}/edit', [UserController::class, 'show'])->name('users.edit');
    Route::post('/{user}/edit', [UserController::class, 'edit'])->name('users.edit.post');
});



