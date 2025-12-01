<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all products
        $products = Product::with('responsibleUser')->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product created',
            'data' => $product->load('responsibleUser')
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        // Check if user can view this product
        if (!auth()->user()->can('view_all_products') && $product->responsible_user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $product->load('responsibleUser')
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProductRequest  $request
     * @param  Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Product updated',
            'data' => $product->fresh()->load('responsibleUser')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        // Check permission
        if (!auth()->user()->can('delete_products')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted'
        ]);
    }

    /**
     * Toggle product status (activate/suspend)
     *
     * @param  Product  $product
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus(Product $product)
    {
        // Check if user can suspend/activate products
        if (!auth()->user()->can('suspend_products') && !auth()->user()->can('activate_products')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $product->status = $product->status === 'active' ? 'suspended' : 'active';
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product status updated',
            'status' => $product->status
        ]);
    }
}
