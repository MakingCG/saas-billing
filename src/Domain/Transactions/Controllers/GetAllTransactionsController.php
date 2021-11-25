<?php
namespace VueFileManager\Subscription\Domain\Transactions\Controllers;

use Illuminate\Routing\Controller;
use VueFileManager\Subscription\Domain\Transactions\Models\Transaction;
use VueFileManager\Subscription\Domain\Transactions\Resources\TransactionCollection;

class GetAllTransactionsController extends Controller
{
    public function __invoke()
    {
        $transactions = Transaction::with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return new TransactionCollection($transactions);
    }
}
