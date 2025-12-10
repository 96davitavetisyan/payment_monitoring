<?php

namespace App\Http\Controllers;

use App\Models\OwnCompany;
use App\Models\File;
use App\Http\Requests\StoreOwnCompanyRequest;
use App\Http\Requests\UpdateOwnCompanyRequest;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OwnCompanyController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $query = OwnCompany::query();

        // Always load files
        $query->with('files');

        // Optionally load contracts with related data
        if ($request->has('with_contracts')) {
            $query->with(['contracts' => function($q) {
                $q->with(['partnerCompany', 'product'])->orderBy('created_at', 'desc');
            }]);
        }

        $companies = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }

    public function store(StoreOwnCompanyRequest $request)
    {
        try {
            $company = OwnCompany::create($request->except('files'));

            // Handle file uploads
            if ($request->hasFile('files')) {
                $this->handleFileUploads($request->file('files'), $company);
            }

            $this->logActivity('create', $company, null, $company->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Own company created successfully',
                'data' => $company->load('files')
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
            'data' => $ownCompany->load(['contracts', 'files'])
        ]);
    }

    public function update(UpdateOwnCompanyRequest $request, OwnCompany $ownCompany)
    {
        try {
            $oldValues = $ownCompany->toArray();
            $ownCompany->update($request->except('files'));

            // Handle file uploads
            if ($request->hasFile('files')) {
                $this->handleFileUploads($request->file('files'), $ownCompany);
            }

            $this->logActivity('update', $ownCompany, $oldValues, $ownCompany->fresh()->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Own company updated successfully',
                'data' => $ownCompany->fresh()->load('files')
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
//        if (!auth()->user()->can('delete_own_companies')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        try {
            $oldValues = $ownCompany->toArray();
            
            // Delete associated files from storage
            foreach ($ownCompany->files as $file) {
                Storage::delete($file->file_path);
                $file->delete();
            }
            
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

    /**
     * Delete a specific file
     */
    public function deleteFile($companyId, $fileId)
    {
        try {
            $company = OwnCompany::findOrFail($companyId);
            $file = $company->files()->findOrFail($fileId);
            
            // Delete file from storage
            Storage::delete($file->file_path);
            
            // Delete file record
            $file->delete();

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle file uploads for a company
     */
    private function handleFileUploads($files, $company)
    {
        foreach ($files as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('own_companies', $fileName, 'public');

            $company->files()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }
}
