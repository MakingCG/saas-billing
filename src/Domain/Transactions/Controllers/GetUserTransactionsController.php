<?php
namespace VueFileManager\Subscription\Domain\Transactions\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use VueFileManager\Subscription\Domain\Transactions\Resources\TransactionCollection;

class GetUserTransactionsController extends Controller
{
    public function __invoke($id): JsonResponse
    {
        $transactions = config('auth.providers.users.model')::find($id)
            ->transactions()
            ->sortable(['created_at' => 'desc'])
            ->paginate(20);

        return response()->json(new TransactionCollection($transactions));
    }
}
