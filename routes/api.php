<?php

use Domain\Plans\Controllers\PlansController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/subscription', 'middleware' => ['api', 'auth:sanctum']], function () {
    Route::apiResource('/plans', PlansController::class);
});
