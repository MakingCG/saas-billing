<?php

use Illuminate\Support\Facades\Route;
use Domain\Subscriptions\Controllers\CancelSubscriptionController;
use Domain\Subscriptions\Controllers\ResumeSubscriptionController;
use VueFileManager\Subscription\Support\Webhooks\WebhooksController;
use VueFileManager\Subscription\Domain\Plans\Controllers\PlansController;
use VueFileManager\Subscription\Domain\Plans\Actions\UpdatePlanFeatureAction;

Route::group(['prefix' => 'api/subscription'], function () {
    Route::group(['middleware' => ['api']], function () {
        Route::post('/{driver}/webhooks', WebhooksController::class);

        Route::post('/cancel', CancelSubscriptionController::class);
        //Route::post('/resume', ResumeSubscriptionController::class);
    });

    Route::group(['middleware' => ['api', 'auth:sanctum']], function () {
        Route::apiResource('/plans', PlansController::class);
        Route::put('/plans/{plan}/features', UpdatePlanFeatureAction::class);
    });
});
