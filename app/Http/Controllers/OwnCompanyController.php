<?php

namespace App\Http\Controllers;

use App\Models\OwnCompany;
use App\Http\Requests\StoreOwnCompanyRequest;
use App\Http\Requests\UpdateOwnCompanyRequest;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class OwnCompanyController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $companies = OwnCompany::orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }

    public function store(StoreOwnCompanyRequest $request)
    {
        try {
            $company = OwnCompany::create($request->validated());
            
            $this->logActivity('create', $company, null, $company->toArray());
            
            return response()->json([
                'success' => true,
                'message' => 'Own company created successfully',
                'data' => $company
            ], 201);
        } catch (\Exception $e) {
            $this->logActivity('create', new OwnCompany(), null, $request->validated(), 'failed', $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create own company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(OwnCompany $ownCompany)
    {
        return response()->json([
            'success' => true,
            'data' => $ownCompany->load('contracts')
        ]);
    }

    public function update(UpdateOwnCompanyRequest $request, OwnCompany $ownCompany)
    {
        try {
            $oldValues = $ownCompany->toArray();
            $ownCompany->update($request->validated());
            
            $this->logActivity('update', $ownCompany, $oldValues, $ownCompany->fresh()->toArray());
            
            return response()->json([
                'success' => true,
                'message' => 'Own company updated successfully',
                'data' => $ownCompany->fresh()
            ]);
        } catch (\Exception $e) {
            $this->logActivity('update', $ownCompany, $oldValues, $request->validated(), 'failed', $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update own company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(OwnCompany $ownCompany)
    {
        if (!auth()->user()->can('delete_own_companies')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $oldValues = $ownCompany->toArray();
            $ownCompany->delete();
            
            $this->logActivity('delete', $ownCompany, $oldValues, null);
            
            return response()->json([
                'success' => true,
                'message' => 'Own company deleted successfully'
            ]);
        } catch (\Exception $e) {
            $this->logActivity('delete', $ownCompany, $oldValues, null, 'failed', $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete own company',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
