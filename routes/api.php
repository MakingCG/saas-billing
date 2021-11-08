<?php

use Illuminate\Support\Facades\Route;
use VueFileManager\Subscription\Domain\Plans\Controllers\PlansController;
use VueFileManager\Subscription\Support\Webhooks\WebhooksController;

Route::group(['prefix' => 'api/subscription'], function () {

    Route::group(['middleware' => ['api']], function () {
        Route::post('/webhooks', WebhooksController::class);
    });

    Route::group(['middleware' => ['api', 'auth:sanctum']], function () {
        Route::apiResource('/plans', PlansController::class);
    });
});
