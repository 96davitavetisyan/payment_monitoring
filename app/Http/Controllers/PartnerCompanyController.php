<?php

namespace App\Http\Controllers;

use App\Models\PartnerCompany;
use App\Http\Requests\StorePartnerCompanyRequest;
use App\Http\Requests\UpdatePartnerCompanyRequest;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class PartnerCompanyController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $query = PartnerCompany::query();

        // Optionally load contracts with related data
        if ($request->has('with_contracts')) {
            $query->with(['contracts' => function($q) {
                $q->with(['ownCompany', 'product'])->orderBy('created_at', 'desc');
            }]);
        }

        $companies = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }

    public function store(StorePartnerCompanyRequest $request)
    {
        try {
            $company = PartnerCompany::create($request->validated());

            $this->logActivity('create', $company, null, $company->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Partner company created successfully',
                'data' => $company
            ], 201);
        } catch (\Exception $e) {
            $this->logActivity('create', new PartnerCompany(), null, $request->validated(), 'failed', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create partner company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(PartnerCompany $partnerCompany)
    {
        return response()->json([
            'success' => true,
            'data' => $partnerCompany->load('contracts')
        ]);
    }

    public function update(UpdatePartnerCompanyRequest $request, PartnerCompany $partnerCompany)
    {
        try {
            $oldValues = $partnerCompany->toArray();
            $partnerCompany->update($request->validated());

            $this->logActivity('update', $partnerCompany, $oldValues, $partnerCompany->fresh()->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Partner company updated successfully',
                'data' => $partnerCompany->fresh()
            ]);
        } catch (\Exception $e) {
            $this->logActivity('update', $partnerCompany, $oldValues, $request->validated(), 'failed', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update partner company',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(PartnerCompany $partnerCompany)
    {
//        if (!auth()->user()->can('delete_partner_companies')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        try {
            $oldValues = $partnerCompany->toArray();
            $partnerCompany->delete();

            $this->logActivity('delete', $partnerCompany, $oldValues, null);

            return response()->json([
                'success' => true,
                'message' => 'Partner company deleted successfully'
            ]);
        } catch (\Exception $e) {
            $this->logActivity('delete', $partnerCompany, $oldValues, null, 'failed', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete partner company',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
