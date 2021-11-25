<?php

namespace VueFileManager\Subscription\Domain\Plans\Controllers;

use Illuminate\Routing\Controller;
use VueFileManager\Subscription\Domain\Plans\Models\Plan;
use VueFileManager\Subscription\Domain\Plans\Resources\PlanCollection;

class GetPlansController extends Controller
{
    public function __invoke()
    {
        $plans = Plan::where('visible', true)
            ->paginate(20);

        return new PlanCollection($plans);
    }
}
