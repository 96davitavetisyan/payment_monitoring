<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies
     */
    public function index()
    {
        $companies = Company::with(['product', 'activeSubscription'])->get();

        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }

    /**
     * Store a newly created company
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'product_id' => 'required|exists:products,id'
        ]);

        $company = Company::create($validated);

        return response()->json([
            'success' => true,
            'data' => $company->load(['product', 'activeSubscription']),
            'message' => 'Company created successfully'
        ], 201);
    }

    /**
     * Display the specified company
     */
    public function show(Company $company)
    {
        $company->load(['product', 'subscriptions', 'transactions.project']);

        return response()->json([
            'success' => true,
            'data' => $company
        ]);
    }

    /**
     * Update the specified company
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'product_id' => 'sometimes|required|exists:products,id'
        ]);

        $company->update($validated);

        return response()->json([
            'success' => true,
            'data' => $company->load(['product', 'activeSubscription']),
            'message' => 'Company updated successfully'
        ]);
    }

    /**
     * Remove the specified company
     */
    public function destroy(Company $company)
    {
        $company->delete();

        return response()->json([
            'success' => true,
            'message' => 'Company deleted successfully'
        ]);
    }

    /**
     * Get subscriptions for a company
     */
    public function subscriptions(Company $company)
    {
        $subscriptions = $company->subscriptions()->orderBy('starts_from', 'desc')->get();

        return response()->json([
            'success' => true,
            'company' => $company,
            'subscriptions' => $subscriptions
        ]);
    }
}
