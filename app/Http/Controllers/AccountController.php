<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Account::with(['accountType', 'parent', 'children']);

        // Filter by account type
        if ($request->has('account_type_id')) {
            $query->where('account_type_id', $request->account_type_id);
        }

        // Filter by active status
        if ($request->has('active_only')) {
            $query->active();
        }

        // Filter by parent
        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null' || $request->parent_id === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'code');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $accounts = $query->get();

        // Optionally include current balance
        if ($request->has('include_balance')) {
            $accounts->each(function ($account) {
                $account->current_balance = $account->getCurrentBalance();
            });
        }

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }

    /**
     * Store a newly created account.
     */
    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = Account::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully.',
            'data' => $account->load(['accountType', 'parent', 'children']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified account.
     */
    public function show(Account $account): JsonResponse
    {
        $account->load(['accountType', 'parent', 'children']);

        // Include current balance
        $account->current_balance = $account->getCurrentBalance();

        return response()->json([
            'success' => true,
            'data' => $account,
        ]);
    }

    /**
     * Update the specified account.
     */
    public function update(StoreAccountRequest $request, Account $account): JsonResponse
    {
        // Prevent editing system accounts
        if ($account->is_system && $request->has('code')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify system account code.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $account->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Account updated successfully.',
            'data' => $account->fresh()->load(['accountType', 'parent', 'children']),
        ]);
    }

    /**
     * Remove the specified account.
     */
    public function destroy(Account $account): JsonResponse
    {
        // Prevent deleting system accounts
        if ($account->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete system accounts.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check if account has children
        if ($account->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account that has child accounts. Please delete or reassign child accounts first.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check if account has journal entries
        if ($account->journalEntryLines()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account that has journal entries. Please use soft delete instead.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully.',
        ]);
    }

    /**
     * Get account types for dropdown/select
     */
    public function getAccountTypes(): JsonResponse
    {
        $accountTypes = AccountType::active()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code', 'normal_balance', 'is_system']);

        return response()->json([
            'success' => true,
            'data' => $accountTypes,
        ]);
    }

    /**
     * Get parent accounts for dropdown/select
     */
    public function getParentAccounts(Request $request): JsonResponse
    {
        $query = Account::active();

        // Exclude current account if editing
        if ($request->has('exclude_id')) {
            $query->where('id', '!=', $request->exclude_id);
        }

        // Filter by account type if provided
        if ($request->has('account_type_id')) {
            $query->where('account_type_id', $request->account_type_id);
        }

        $accounts = $query->orderBy('code')
            ->get(['id', 'code', 'name', 'account_type_id']);

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }
}
