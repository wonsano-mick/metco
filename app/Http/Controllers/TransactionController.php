<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Transaction\TransactionService;
use App\Models\Eloquent\Account;
use App\Models\Eloquent\Transaction;

class TransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
        $this->middleware('auth');
    }

    /**
     * Transfer funds between accounts
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'pin' => 'required|string|size:4', // Transaction PIN for security
        ]);

        // Verify transaction PIN (simplified - implement proper encryption)
        if ($request->pin !== Auth::user()->transaction_pin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid transaction PIN',
            ], 401);
        }

        try {
            $transaction = $this->transactionService->transfer([
                'from_account_id' => $request->from_account_id,
                'to_account_id' => $request->to_account_id,
                'amount' => $request->amount,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfer completed successfully',
                'data' => [
                    'transaction' => $transaction,
                    'reference' => $transaction->transaction_reference,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Withdraw funds
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'method' => 'required|in:cash,check,bank_transfer',
            'pin' => 'required|string|size:4',
        ]);

        if ($request->pin !== Auth::user()->transaction_pin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid transaction PIN',
            ], 401);
        }

        try {
            $transaction = $this->transactionService->withdraw([
                'account_id' => $request->account_id,
                'amount' => $request->amount,
                'description' => $request->description,
                'method' => $request->method,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal completed successfully',
                'data' => [
                    'transaction' => $transaction,
                    'reference' => $transaction->transaction_reference,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Deposit funds
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'method' => 'required|in:cash,check,bank_transfer,wire',
            'external_reference' => 'nullable|string|max:100',
            'pin' => 'required|string|size:4',
        ]);

        if ($request->pin !== Auth::user()->transaction_pin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid transaction PIN',
            ], 401);
        }

        try {
            $transaction = $this->transactionService->deposit([
                'account_id' => $request->account_id,
                'amount' => $request->amount,
                'description' => $request->description,
                'method' => $request->method,
                'external_reference' => $request->external_reference,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deposit completed successfully',
                'data' => [
                    'transaction' => $transaction,
                    'reference' => $transaction->transaction_reference,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reverse a transaction
     */
    public function reverse(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
            'pin' => 'required|string|size:4',
        ]);

        if ($request->pin !== Auth::user()->transaction_pin) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid transaction PIN',
            ], 401);
        }

        try {
            $reversal = $this->transactionService->reverse($id, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Transaction reversed successfully',
                'data' => [
                    'reversal_transaction' => $reversal,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get transaction history for an account
     */
    public function history(Request $request, $accountId)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'type' => 'nullable|in:transfer,withdrawal,deposit,reversal',
            'status' => 'nullable|in:pending,completed,failed,reversed',
            'min_amount' => 'nullable|numeric',
            'max_amount' => 'nullable|numeric',
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|min:5|max:100',
        ]);

        // Verify account ownership
        $account = Account::findOrFail($accountId);
        if ($account->customer_id !== Auth::user()->customer_id && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to account',
            ], 403);
        }

        try {
            $transactions = $this->transactionService->getAccountHistory($accountId, $request->all());

            return response()->json([
                'success' => true,
                'data' => $transactions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get transaction details
     */
    public function show($id)
    {
        $transaction = Transaction::with(['ledgerEntries.account', 'initiator'])
            ->findOrFail($id);

        // Check permissions
        $user = Auth::user();
        $canView = false;

        if ($user->hasRole('admin')) {
            $canView = true;
        } else {
            // Check if user's account is involved in the transaction
            $userAccountIds = Account::where('customer_id', $user->customer_id)
                ->pluck('id')
                ->toArray();

            $transactionAccountIds = $transaction->ledgerEntries->pluck('account_id')->toArray();

            if (array_intersect($userAccountIds, $transactionAccountIds)) {
                $canView = true;
            }
        }

        if (!$canView) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this transaction',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

    /**
     * Get transaction limits for an account
     */
    public function limits($accountId)
    {
        $account = Account::with(['accountType.transactionLimits'])
            ->findOrFail($accountId);

        // Check account ownership
        if ($account->customer_id !== Auth::user()->customer_id && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to account',
            ], 403);
        }

        $limits = $account->accountType->transactionLimits
            ->where('is_active', true)
            ->groupBy('transaction_type');

        return response()->json([
            'success' => true,
            'data' => [
                'account' => $account,
                'limits' => $limits,
            ],
        ]);
    }
}
