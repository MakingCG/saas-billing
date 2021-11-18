<?php
namespace VueFileManager\Subscription\Domain\Transactions;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use VueFileManager\Subscription\Domain\Transactions\Resources\TransactionCollection;

class GetTransactionsController extends Controller
{
    public function __invoke()
    {
        $transactions = Auth::user()
            ->transactions()
            ->orderByDesc('created_at')
            ->get();

        return new TransactionCollection($transactions);
    }
}
