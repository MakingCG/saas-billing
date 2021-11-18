<?php

use Illuminate\Support\Facades\Route;
use Domain\Transactions\GetTransactionsController;
use VueFileManager\Subscription\Support\Webhooks\WebhooksController;
use VueFileManager\Subscription\Domain\Plans\Controllers\PlansController;
use VueFileManager\Subscription\Domain\Plans\Actions\UpdatePlanFeatureAction;
use VueFileManager\Subscription\Domain\Subscriptions\Controllers\SwapSubscriptionController;
use VueFileManager\Subscription\Domain\Subscriptions\Controllers\CancelSubscriptionController;

Route::group(['prefix' => 'api/subscription'], function () {
    Route::group(['middleware' => ['api']], function () {
        Route::post('/{driver}/webhooks', WebhooksController::class);

        Route::post('/cancel', CancelSubscriptionController::class);
        Route::post('/swap/{plan}', SwapSubscriptionController::class);
        //Route::post('/resume', ResumeSubscriptionController::class);
    });

    Route::group(['middleware' => ['api', 'auth:sanctum']], function () {
        // Admin
        Route::apiResource('/plans', PlansController::class);
        Route::put('/plans/{plan}/features', UpdatePlanFeatureAction::class);

        // User
        Route::get('/transactions', GetTransactionsController::class);
    });
});
