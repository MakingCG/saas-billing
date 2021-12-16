<?php
namespace Domain\BillingAlerts;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;

class BillingAlertController extends Controller
{
    public function store(Request $request): Response|Application|ResponseFactory
    {
        $request
            ->user()
            ->billingAlert()
            ->create(
                $request->all()
            );

        return response('Done', 201);
    }
}
