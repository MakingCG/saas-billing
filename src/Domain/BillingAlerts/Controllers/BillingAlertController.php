<?php
namespace VueFileManager\Subscription\Domain\BillingAlerts\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use VueFileManager\Subscription\Domain\BillingAlerts\Models\BillingAlert;

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

    public function update(Request $request, BillingAlert $billingAlert): Response|Application|ResponseFactory
    {
        $billingAlert->update(
            $request->only('amount')
        );

        return response('Done', 204);
    }

    public function destroy(BillingAlert $billingAlert): Response|Application|ResponseFactory
    {
        $billingAlert->delete();

        return response('Done', 204);
    }
}
