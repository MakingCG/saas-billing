<?php

namespace Domain\Webhooks\Controllers;

use Illuminate\Http\Request;
use Support\EngineManager;

class WebhookController
{
    public function __invoke(Request $request)
    {
        resolve(EngineManager::class)->webhook($request);
    }
}
