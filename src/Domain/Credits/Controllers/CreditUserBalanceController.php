<?php

namespace VueFileManager\Subscription\Domain\Credits\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CreditUserBalanceController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $user = config('auth.providers.users.model')::find($id);

        // Credit user balance
        $user->creditBalance($request->input('amount'));

        // Store transaction
        $user->transactions()->create([
            'status'   => 'completed',
            'type'     => 'credit',
            'driver'   => 'system',
            'note'     => __('Bonus'),
            'currency' => $user->balance->currency,
            'amount'   => $request->input('amount'),
        ]);

        return response('Done', 204);
    }
}
