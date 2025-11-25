<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Project $project)
    {
        // Check permission
        if (!auth()->user()->can('view_transactions')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $active = $project->transactions()->with('company')->active()->get();
        $history = $project->transactions()->with('company')->history()->get();

        return response()->json([
            'active' => $active,
            'history' => $history
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTransactionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTransactionRequest $request, Project $project)
    {
        $data = $request->validated();

        // Handle file upload
        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('transactions');
        }

        // Handle contract file upload
        if ($request->hasFile('contract_file')) {
            $data['contract_file'] = $request->file('contract_file')->store('contracts');
        }

        $transaction = $project->transactions()->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Transaction created',
            'data' => $transaction
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTransactionRequest  $request
     * @param  Project  $project
     * @param  Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTransactionRequest $request, Project $project, Transaction $transaction)
    {
        $data = $request->validated();

        // Handle file upload
        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('transactions');
        }

        // Handle contract file upload
        if ($request->hasFile('contract_file')) {
            $data['contract_file'] = $request->file('contract_file')->store('contracts');
        }

        $transaction->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated',
            'data' => $transaction->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Project  $project
     * @param  Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project, Transaction $transaction)
    {
        // Check permission
        if (!auth()->user()->can('delete_transactions')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted'
        ]);
    }

    /**
     * Toggle active status of transaction (move to history or back to active)
     *
     * @param  Project  $project
     * @param  Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus(Project $project, Transaction $transaction)
    {
        // Check permission
        if (!auth()->user()->can('edit_transactions')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $transaction->is_active = !$transaction->is_active;
        $transaction->save();

        return response()->json([
            'success' => true,
            'message' => 'Transaction status updated',
            'is_active' => $transaction->is_active
        ]);
    }
}
