<?php
namespace Domain\Plans\Controllers;

use Domain\Plans\Models\Plan;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Domain\Plans\DTO\CreatePlanData;
use Domain\Plans\Requests\StorePlanRequest;
use Domain\Plans\Actions\CreatePlansViaDriversAPIAction;

class PlansController extends Controller
{
    public function store(
        StorePlanRequest $request,
        CreatePlansViaDriversAPIAction $createPlansViaDriversAPI,
    ): Response {
        $data = CreatePlanData::fromRequest($request);

        $plan = Plan::create([
            'name'        => $data->name,
            'description' => $data->description,
            'interval'    => $data->interval,
            'price'       => $data->price,
            'amount'      => $data->amount,
        ]);

        // Create plan in available gateways
        $createPlansViaDriversAPI
            ->onQueue()
            ->execute($data, $plan);

        return response($plan, 201);
    }
}
