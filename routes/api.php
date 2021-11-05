<?php

use Domain\Webhooks\Controllers\WebhookController;
use Domain\Plans\Controllers\PlansController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/subscription'], function () {

    Route::group(['middleware' => ['api']], function () {
        Route::post('/webhook', WebhookController::class);
    });

    Route::group(['middleware' => ['api', 'auth:sanctum']], function () {
        Route::apiResource('/plans', PlansController::class);
    });
});
