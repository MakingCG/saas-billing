<?php
namespace VueFileManager\Subscription\Domain\Transactions\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use VueFileManager\Subscription\Domain\Transactions\Resources\TransactionCollection;

class GetUserTransactionsController extends Controller
{
    public function __invoke($id)
    {
        $transactions = config('auth.providers.users.model')::find($id)
            ->transactions()
            ->orderByDesc('created_at')
            ->paginate(20);

        return new TransactionCollection($transactions);
    }
}
