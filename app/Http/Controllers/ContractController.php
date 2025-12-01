<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $contracts = Contract::with(['partnerCompany', 'ownCompany', 'product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contracts
        ]);
    }

    public function store(StoreContractRequest $request)
    {
        try {
            $data = $request->validated();

            // Handle contract file upload
            if ($request->hasFile('contract_file')) {
                $data['contract_file'] = $request->file('contract_file')->store('contracts');
            }

            $contract = Contract::create($data);

            $this->logActivity('create', $contract, null, $contract->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Contract created successfully',
                'data' => $contract->load(['partnerCompany', 'ownCompany', 'product'])
            ], 201);
        } catch (\Exception $e) {
            $this->logActivity('create', new Contract(), null, $request->except('contract_file'), 'failed', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create contract',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Contract $contract)
    {
        return response()->json([
            'success' => true,
            'data' => $contract->load(['partnerCompany', 'ownCompany', 'product', 'transactions'])
        ]);
    }

    public function update(UpdateContractRequest $request, Contract $contract)
    {
        try {
            $oldValues = $contract->toArray();
            $data = $request->validated();

            // Handle contract file upload
            if ($request->hasFile('contract_file')) {
                // Delete old file if exists
                if ($contract->contract_file) {
                    \Storage::delete($contract->contract_file);
                }
                $data['contract_file'] = $request->file('contract_file')->store('contracts');
            }

            $contract->update($data);

            $this->logActivity('update', $contract, $oldValues, $contract->fresh()->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Contract updated successfully',
                'data' => $contract->fresh()->load(['partnerCompany', 'ownCompany', 'product'])
            ]);
        } catch (\Exception $e) {
            $this->logActivity('update', $contract, $oldValues, $request->except('contract_file'), 'failed', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update contract',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Contract $contract)
    {
//        if (!auth()->user()->can('delete_contracts')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        try {
            $oldValues = $contract->toArray();

            // Delete contract file if exists
            if ($contract->contract_file) {
                \Storage::delete($contract->contract_file);
            }

            $contract->delete();

            $this->logActivity('delete', $contract, $oldValues, null);

            return response()->json([
                'success' => true,
                'message' => 'Contract deleted successfully'
            ]);
        } catch (\Exception $e) {
            $this->logActivity('delete', $contract, $oldValues, null, 'failed', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete contract',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
