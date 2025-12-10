<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Feedback;
use App\Http\Requests\StoreFeedbackRequest;
use App\Http\Requests\UpdateFeedbackRequest;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Display a listing of feedbacks for a product.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Product $product)
    {
        // Check permission
//        if (!auth()->user()->can('view_feedback')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        $feedbacks = $product->feedbacks()->with('accountManager')->latest()->get();
dd($feedbacks);
        return response()->json([
            'success' => true,
            'data' => $feedbacks
        ]);
    }

    /**
     * Store a newly created feedback in storage.
     *
     * @param  \App\Http\Requests\StoreFeedbackRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFeedbackRequest $request, Product $product)
    {
        $data = $request->validated();
        $data['account_manager_id'] = auth()->id();

        // Handle file upload
        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('feedbacks', 'public');
        }

        $feedback = $product->feedbacks()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Feedback created',
            'data' => $feedback->load('accountManager')
        ], 201);
    }

    /**
     * Update the specified feedback in storage.
     *
     * @param  \App\Http\Requests\UpdateFeedbackRequest  $request
     * @param  Product  $product
     * @param  Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFeedbackRequest $request, Product $product, Feedback $feedback)
    {
        $data = $request->validated();

        // Handle file upload
        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('feedbacks', 'public');
        }

        $feedback->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Feedback updated',
            'data' => $feedback->fresh()->load('accountManager')
        ]);
    }

    /**
     * Remove the specified feedback from storage.
     *
     * @param  Product  $product
     * @param  Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product, Feedback $feedback)
    {
        // Check permission - only the creator or admin can delete
//        if (!auth()->user()->can('manage_feedback') && $feedback->account_manager_id !== auth()->id()) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        $feedback->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feedback deleted'
        ]);
    }
}
