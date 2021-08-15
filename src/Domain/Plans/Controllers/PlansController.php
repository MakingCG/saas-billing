<?php
namespace Domain\Plans\Controllers;

use Support\EngineManager;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Domain\Plans\DTO\CreatePlanData;
use Domain\Plans\Requests\StorePlanRequest;

class PlansController extends Controller
{
    public function __construct(
        public EngineManager $subscription,
    ) {
    }

    public function store(StorePlanRequest $request): Response
    {
        $plan = $this->subscription->createPlan(
            CreatePlanData::fromRequest($request)
        );

        return response($plan, 201);
    }
}
