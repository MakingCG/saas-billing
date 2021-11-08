<?php
namespace VueFileManager\Subscription\Support\Webhooks;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use VueFileManager\Subscription\Support\EngineManager;
use Illuminate\Http\Request;

class WebhooksController extends Controller
{
    public function __invoke(Request $request)
    {
        Log::info($request->all());

        resolve(EngineManager::class)->webhook($request);
    }
}
