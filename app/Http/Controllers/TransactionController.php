<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ProcessMoneyTransferAction;
use App\Exceptions\TransactionException;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\User;
use App\Repositories\TransactionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final class TransactionController extends Controller
{
    public function index(Request $request, TransactionRepository $transactionRepository): Response
    {
        $user = $request->user();
        assert($user instanceof User);

        return Inertia::render('transactions/Index', [
            'balance' => number_format((float) $user->balance, 2, '.', ''),
            'transactions' => fn () => TransactionResource::collection(
                $transactionRepository->getUserTransactions($user)
            ),
            'users' => fn (): Collection => $transactionRepository->getRecipients($user),
            'commission_rate' => config('wallet.commission_rate'),
        ]);
    }

    public function store(StoreTransactionRequest $request, ProcessMoneyTransferAction $processMoneyTransfer): RedirectResponse
    {
        $user = $request->user();
        assert($user instanceof User);

        try {
            $processMoneyTransfer->handle(
                $user,
                $request->integer('receiver_id'),
                $request->float('amount')
            );

            return back()->with('success', 'Transaction completed successfully.');
        } catch (TransactionException $e) {
            return back()->withErrors([
                'transaction' => $e->getMessage(),
            ]);
        } catch (Throwable) {
            return back()->withErrors([
                'transaction' => 'Something went wrong. Please try again later.',
            ]);
        }
    }
}
