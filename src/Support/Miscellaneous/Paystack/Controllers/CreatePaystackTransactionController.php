<?php

namespace Support\Miscellaneous\Paystack\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use VueFileManager\Subscription\Domain\Plans\Models\PlanDriver;
use VueFileManager\Subscription\Support\Services\PayStackHttpClient;

class CreatePaystackTransactionController
{
    use PayStackHttpClient;

    public function __invoke(Request $request)
    {
        $user = Auth::user();

        // Get gateway plan id
        $plan = PlanDriver::where('driver_plan_id', $request->input('planCode'))
            ->first();

        return $this->post('/transaction/initialize', [
            'amount'       => $plan->amount * 100,
            'email'        => $user->email,
            'callback_url' => url('/user/settings/billing'),
            'plan'         => $request->input('planCode'),
            'channels'     => ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer'],
        ]);
    }

}
