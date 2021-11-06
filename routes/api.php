<?php

use Illuminate\Support\Facades\Route;
use Domain\Plans\Controllers\PlansController;
use Support\Webhooks\WebhookController;

Route::group(['prefix' => 'api/subscription'], function () {

    Route::group(['middleware' => ['api']], function () {

        // TODO: resolve action issue
        Route::post('/webhook', [WebhookController::class, 'store']);
    });

    Route::group(['middleware' => ['api', 'auth:sanctum']], function () {
        Route::apiResource('/plans', PlansController::class);
    });
});
