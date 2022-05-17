<?php
namespace VueFileManager\Subscription\Domain\Transactions\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use VueFileManager\Subscription\Domain\Transactions\Resources\TransactionCollection;

class GetTransactionsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $transactions = auth()->user()
            ->transactions()
            ->sortable(['created_at' => 'desc'])
            ->paginate(20);

        return response()->json(new TransactionCollection($transactions));
    }
}
