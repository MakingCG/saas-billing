<?php
namespace Support\Webhooks;

use Support\EngineManager;
use Illuminate\Http\Request;

class WebhookController
{
    public function __invoke(Request $request)
    {
        resolve(EngineManager::class)->webhook($request);
    }
}
