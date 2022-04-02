<?php
namespace VueFileManager\Subscription\Domain\Plans\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\Resources\PlanResource;
use VueFileManager\Subscription\Domain\Plans\DTO\CreateFixedPlanData;
use VueFileManager\Subscription\Domain\Plans\Resources\PlanCollection;
use VueFileManager\Subscription\Domain\Plans\DTO\CreateMeteredPlanData;
use VueFileManager\Subscription\Domain\Plans\Requests\StorePlanRequest;
use VueFileManager\Subscription\Domain\Plans\Requests\UpdatePlanRequest;
use VueFileManager\Subscription\Domain\Plans\Actions\StoreFixedPlanAction;
use VueFileManager\Subscription\Domain\Plans\Actions\StoreMeteredPlanAction;
use VueFileManager\Subscription\Domain\Plans\Actions\DeletePlansFromPaymentServiceAction;

class PlansController extends Controller
{
    /**
     * Show all visible subscription plans
     */
    public function index(): PlanCollection
    {
        $plans = Plan::sortable(['created_at' => 'desc'])
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
        StoreFixedPlanAction $storeFixedPlan,
        StoreMeteredPlanAction $storeMeteredPlan,
    ): Response {
        if (is_demo()) {
            return response('Done', 201);
        }

        // Create fixed Plan
        if ($request->input('type') === 'fixed') {
            // Map data into DTO
            $fixedPlanData = CreateFixedPlanData::fromRequest($request);

            // Store plan to the internal database
            $plan = $storeFixedPlan($fixedPlanData);
        }

        // Create metered Plan
        if ($request->input('type') === 'metered') {
            // Map data into DTO
            $meteredPlanData = CreateMeteredPlanData::fromRequest($request);

            // Store plan to the internal database
            $plan = $storeMeteredPlan($meteredPlanData);
        }

        return response(new PlanResource($plan), 201);
    }

    /**
     * Update only single attribute of subscription plan
     */
    public function update(
        UpdatePlanRequest $request,
        Plan $plan,
    ): Response {
        if (is_demo()) {
            return response(new PlanResource($plan), 200);
        }

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
        if (is_demo()) {
            return response('Deleted', 204);
        }

        // Delete via API when plan type is fixed
        if ($plan->type === 'fixed') {
            $deletePlansFromPaymentService($plan);
        }

        // Archive plan if there are some subscribed customers
        if ($plan->subscriptions()->exists()) {
            $plan->update(['status' => 'archived']);
        }

        // Delete plan if there isn't any subscribed customer
        if ($plan->subscriptions()->doesntExist()) {
            $plan->delete();
        }

        return response('Deleted', 204);
    }
}
