<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ProcessMoneyTransferAction;
use App\Exceptions\TransactionException;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Repositories\TransactionRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class TransactionController extends Controller
{
    public function index(Request $request, TransactionRepository $transactionRepository): Response
    {
        $user = $request->user();

        return Inertia::render('transactions/Index', [
            'balance' => number_format((float) $user->balance, 2, '.', ''),
            'transactions' => fn () => TransactionResource::collection(
                $transactionRepository->getUserTransactions($user)
            ),
            'users' => fn () => $transactionRepository->getRecipients($user),
        ]);
    }

    public function store(StoreTransactionRequest $request, ProcessMoneyTransferAction $processMoneyTransfer)
    {
        try {
            $processMoneyTransfer->handle(
                $request->user(),
                (int) $request->validated('receiver_id'),
                (float) $request->validated('amount')
            );

            return back()->with('success', 'Transaction completed successfully.');
        } catch (TransactionException $e) {
            return back()->withErrors([
                'transaction' => $e->getMessage(),
            ]);
        }
    }
}
