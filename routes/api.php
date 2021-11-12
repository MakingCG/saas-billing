<?php

use Illuminate\Support\Facades\Route;
use VueFileManager\Subscription\Domain\Plans\Actions\UpdatePlanFeatureAction;
use VueFileManager\Subscription\Support\Webhooks\WebhooksController;
use VueFileManager\Subscription\Domain\Plans\Controllers\PlansController;

Route::group(['prefix' => 'api/subscription'], function () {
    Route::group(['middleware' => ['api']], function () {
        Route::post('/{driver}/webhooks', WebhooksController::class);
    });

    Route::group(['middleware' => ['api', 'auth:sanctum']], function () {
        Route::apiResource('/plans', PlansController::class);
        Route::put('/plans/{plan}/features', UpdatePlanFeatureAction::class);
    });
});
