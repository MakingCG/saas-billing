<?php
namespace VueFileManager\Subscription\Domain\Transactions\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use VueFileManager\Subscription\Domain\Transactions\Models\Transaction;
use VueFileManager\Subscription\Domain\Transactions\Resources\TransactionCollection;

class GetAllTransactionsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $transactions = Transaction::with('user')
            ->sortable(['created_at' => 'desc'])
            ->paginate(20);

        return response()->json(new TransactionCollection($transactions));
    }
}
