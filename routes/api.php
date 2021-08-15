<?php

use Illuminate\Support\Facades\Route;
use Domain\Plans\Controllers\PlansController;

Route::group(['prefix' => 'api/subscription', 'middleware' => ['api', 'auth:sanctum']], function () {
    Route::apiResource('/plans', PlansController::class);
});
