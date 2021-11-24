<?php

use Illuminate\Support\Facades\Route;
use VueFileManager\Subscription\Support\Webhooks\WebhooksController;
use VueFileManager\Subscription\Domain\Plans\Controllers\PlansController;
use VueFileManager\Subscription\Domain\Plans\Actions\UpdatePlanFeatureAction;
use VueFileManager\Subscription\Domain\Transactions\Controllers\GetTransactionsController;
use VueFileManager\Subscription\Domain\Subscriptions\Controllers\GetSubscriptionController;
use VueFileManager\Subscription\Domain\Subscriptions\Controllers\SwapSubscriptionController;
use VueFileManager\Subscription\Domain\Subscriptions\Controllers\CancelSubscriptionController;
use VueFileManager\Subscription\Domain\Transactions\Controllers\GetUserTransactionsController;
use VueFileManager\Subscription\Domain\Subscriptions\Controllers\GetUserSubscriptionController;

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
        Route::get('/detail', GetSubscriptionController::class);

        // User admin
        Route::get('/users/{id}/transactions', GetUserTransactionsController::class);
        Route::get('/users/{id}/subscription', GetUserSubscriptionController::class);
    });
});
