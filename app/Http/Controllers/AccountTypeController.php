<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountTypeRequest;
use App\Models\AccountType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AccountTypeController extends Controller
{
    /**
     * Display a listing of account types.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AccountType::query();

        // Filter by active status
        if ($request->has('active_only')) {
            $query->active();
        }

        // Filter by system status
        if ($request->has('is_system')) {
            if ($request->is_system === '1' || $request->is_system === true) {
                $query->system();
            } else {
                $query->nonSystem();
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
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $accountTypes = $query->get();

        return response()->json([
            'success' => true,
            'data' => $accountTypes,
        ]);
    }

    /**
     * Store a newly created account type.
     */
    public function store(StoreAccountTypeRequest $request): JsonResponse
    {
        $accountType = AccountType::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Account type created successfully.',
            'data' => $accountType->load('accounts'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified account type.
     */
    public function show(AccountType $accountType): JsonResponse
    {
        $accountType->load('accounts');

        return response()->json([
            'success' => true,
            'data' => $accountType,
        ]);
    }

    /**
     * Update the specified account type.
     */
    public function update(StoreAccountTypeRequest $request, AccountType $accountType): JsonResponse
    {
        // Prevent editing system account types
        if ($accountType->is_system) {
            // Allow updating only certain fields for system account types
            $allowedFields = ['description', 'is_active'];
            $data = array_intersect_key($request->validated(), array_flip($allowedFields));
            
            // Prevent changing code, name, or normal_balance for system accounts
            if ($request->has('code') && $request->code !== $accountType->code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify code of system account type.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            if ($request->has('name') && $request->name !== $accountType->name) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify name of system account type.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            if ($request->has('normal_balance') && $request->normal_balance !== $accountType->normal_balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot modify normal balance of system account type.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $accountType->update($data);
        } else {
            $accountType->update($request->validated());
        }

        return response()->json([
            'success' => true,
            'message' => 'Account type updated successfully.',
            'data' => $accountType->fresh()->load('accounts'),
        ]);
    }

    /**
     * Remove the specified account type.
     */
    public function destroy(AccountType $accountType): JsonResponse
    {
        // Prevent deleting system account types
        if ($accountType->is_system) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete system account types.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check if account type has accounts
        if ($accountType->accounts()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account type that has associated accounts.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $accountType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account type deleted successfully.',
        ]);
    }
}
