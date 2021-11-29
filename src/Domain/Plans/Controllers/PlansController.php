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
use VueFileManager\Subscription\Domain\Plans\Actions\StorePlanForPaymentServiceAction;
use VueFileManager\Subscription\Domain\Plans\Actions\DeletePlansFromPaymentServiceAction;

class PlansController extends Controller
{
    /**
     * Show all visible subscription plans
     */
    public function index(): PlanCollection
    {
        $plans = Plan::where('status', 'active')
            ->sortable(['created_at' => 'desc'])
            ->paginate(20);

        return new PlanCollection($plans);
    }

    /**
     * Update only single attribute of subscription plan
     */
    public function show(
        Plan $plan,
    ): Response {
        return response(new PlanResource($plan), 200);
    }

    /**
     * Store new subscription plan
     */
    public function store(
        StorePlanRequest $request,
        StorePlanForPaymentServiceAction $storePlanForPaymentService,
    ): Response {
        // Map data into DTO
        $data = CreatePlanData::fromRequest($request);

        // Store plan to the internal database
        $plan = $storePlanForPaymentService($data);

        return response(new PlanResource($plan), 201);
    }

    /**
     * Update only single attribute of subscription plan
     */
    public function update(
        UpdatePlanRequest $request,
        Plan $plan,
    ): Response {
        $plan->update($request->all());

        return response(new PlanResource($plan), 200);
    }

    /**
     * Delete subscription plan
     */
    public function destroy(
        Plan $plan,
        DeletePlansFromPaymentServiceAction $deletePlansFromPaymentService
    ): Response {
        $deletePlansFromPaymentService($plan);

        $plan->update([
            'status' => 'archived',
        ]);

        return response('Deleted', 204);
    }
}
