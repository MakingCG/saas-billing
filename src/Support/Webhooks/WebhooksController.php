<?php
namespace VueFileManager\Subscription\Support\Webhooks;

use Illuminate\Routing\Controller;
use Support\EngineManager;
use Illuminate\Http\Request;

class WebhooksController extends Controller
{
    public function __invoke(Request $request)
    {
        resolve(EngineManager::class)->webhook($request);
    }
}
