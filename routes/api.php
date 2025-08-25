<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CampanhaController;

Route::post('/webhook', [WebhookController::class, 'handle'])->name('webhook.handle');

Route::get('/webhook/status/{uuid}', [WebhookController::class, 'get'])->name('webhook.get');