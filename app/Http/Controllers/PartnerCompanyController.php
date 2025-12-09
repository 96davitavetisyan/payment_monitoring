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

        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Optionally load contracts with related data
        if ($request->has('with_contracts')) {
            $query->with(['contracts' => function($q) {
                $q->with(['ownCompany', 'product'])->orderBy('created_at', 'desc');
            }]);
        }

        // Optionally load employees
        if ($request->has('with_employees')) {
            $query->with('employees');
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
            $data = $request->validated();
            $employees = $data['employees'] ?? [];
            unset($data['employees']);

            $company = PartnerCompany::create($data);

            // Create employees if provided
            if (!empty($employees)) {
                foreach ($employees as $employeeData) {
                    if (!empty($employeeData['name'])) {
                        $company->employees()->create($employeeData);
                    }
                }
            }

            $this->logActivity('create', $company, null, $company->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Partner company created successfully',
                'data' => $company->load('employees')
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
            $data = $request->validated();
            $employees = $data['employees'] ?? [];
            unset($data['employees']);

            $partnerCompany->update($data);

            // Update employees
            // Delete existing employees and recreate them
            $partnerCompany->employees()->delete();

            if (!empty($employees)) {
                foreach ($employees as $employeeData) {
                    if (!empty($employeeData['name'])) {
                        $partnerCompany->employees()->create($employeeData);
                    }
                }
            }

            $this->logActivity('update', $partnerCompany, $oldValues, $partnerCompany->fresh()->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Partner company updated successfully',
                'data' => $partnerCompany->fresh()->load('employees')
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
