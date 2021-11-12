<?php
namespace VueFileManager\Subscription\Domain\Plans\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\DTO\CreatePlanData;
use VueFileManager\Subscription\Domain\Plans\Resources\PlanResource;
use VueFileManager\Subscription\Domain\Plans\Resources\PlanCollection;
use VueFileManager\Subscription\Domain\Plans\Requests\StorePlanRequest;
use VueFileManager\Subscription\Domain\Plans\Requests\UpdatePlanRequest;
use VueFileManager\Subscription\Domain\Plans\Actions\StorePlanAndCreateDriverVersionAction;

class PlansController extends Controller
{
    public function index()
    {
        $plans = Plan::where('visible', true)->get();

        return new PlanCollection($plans);
    }

    public function store(
        StorePlanRequest $request,
        StorePlanAndCreateDriverVersionAction $storePlanAndCreateDriverVersion,
    ): Response {
        // Map data into DTO
        $data = CreatePlanData::fromRequest($request);

        // Store plan to the internal database
        $plan = $storePlanAndCreateDriverVersion($data);

        return response(new PlanResource($plan), 201);
    }

    public function update(
        UpdatePlanRequest $request,
        Plan $plan,
    ): Response {
        // Update plan
        $plan->update($request->all());

        return response(new PlanResource($plan), 200);
    }
}
