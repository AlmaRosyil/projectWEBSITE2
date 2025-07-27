<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class TransactionController extends Controller
{
    //
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function index()
    {
        $fields = ['*'];
        $transactions = $this->transactionService->getAll($fields);
        return response()->json(TransactionResource::collection($transactions));

    }

    public function store(TransactionRequest $request)
    {
        $transaction = $this->transactionService->createTransaction($request->validated());

        return response()->json([
            'message' => 'Transaction recorded successfuly',
            'data' => $transaction,
        ], 201);
    }

    public function show(int $id)
    {
        try {
            $fields = ['*'];
            $transaction = $this->transactionService->getTransactionById($id, $fields);
            return response()->json(new TransactionResource($transaction));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'transaction not found',
            ], 404);
        }
    }

    public function getTransactionByMerchant()
    {
        $user = Auth::user();

        if (!$user || !$user->merchant) {
            return response()->json(['message' => 'No merchant assigned'], 403);
        }

        $merchantId = $user->merchant->id;
        $transactions = $this->transactionService->getTransactionsByMerchant($merchantId);

        return response()->json($transactions);
    }

    // app/Http/Controllers/TransactionController.php
public function getTransactionsByMerchant()      // ← plural “Transactions”
{
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Unauthenticated.'], 500);
    }

    if (!$user->merchant) {
        return response()->json(['message' => 'User does not own a merchant.'], 403);
    }

    $merchantId = $user->merchant->id;

    // pastikan nama service method persis:
    $tx = $this->transactionService->getTransactionsByMerchant($merchantId);

    return response()->json(TransactionResource::collection($tx));
}



}
