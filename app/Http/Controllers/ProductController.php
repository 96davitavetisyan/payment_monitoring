<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products
     */
    public function index()
    {
        $products = Product::with('companies')->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get companies using a specific product
     */
    public function companies(Product $product)
    {
        $companies = $product->companies()
            ->with(['activeSubscription'])
            ->get()
            ->map(function ($company) {
                $subscription = $company->activeSubscription;
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'contact_email' => $company->contact_email,
                    'contact_phone' => $company->contact_phone,
                    'is_active' => $company->is_active,
                    'subscription' => $subscription ? [
                        'starts_from' => $subscription->starts_from,
                        'price_per_month' => $subscription->price_per_month,
                        'status' => $subscription->status
                    ] : null,
                    'created_at' => $company->created_at
                ];
            });

        return response()->json([
            'success' => true,
            'product' => $product,
            'companies' => $companies,
            'total_companies' => $companies->count()
        ]);
    }

    /**
     * Create a new product
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Product created',
            'data' => $product
        ], 201);
    }

    /**
     * Update a product
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Product updated',
            'data' => $product
        ]);
    }

    /**
     * Delete a product
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted'
        ]);
    }
}
