<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionFile;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    use LogsActivity;

    public function index(Request $request)
    {
        $query = Transaction::with(['contract.partnerCompany', 'contract.ownCompany', 'contract.product', 'files']);

        if ($request->has('contract_id')) {
            $query->where('contract_id', $request->contract_id);
        }

        if ($request->has('status')) {
            $query->where('payment_status', $request->status);
        }

        $transactions = $query->orderBy('invoice_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'invoice_number' => 'required|string|unique:transactions,invoice_number',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after:invoice_date',
            'amount' => 'required|numeric|min:0',
            'payment_status' => 'sometimes|in:pending,paid,late,overdue,cancelled',
            'notes' => 'nullable|string',
            'files.*' => 'nullable|file|max:10240',
        ]);

        try {
            $transaction = Transaction::create($request->except('files'));

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('transaction_files');

                    TransactionFile::create([
                        'transaction_id' => $transaction->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            $this->logActivity('create', $transaction, null, $transaction->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully',
                'data' => $transaction->load(['contract', 'files'])
            ], 201);
        } catch (\Exception $e) {
            $this->logActivity('create', new Transaction(), null, $request->except('files'), 'failed', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Transaction $transaction)
    {
        return response()->json([
            'success' => true,
            'data' => $transaction->load(['contract.partnerCompany', 'contract.ownCompany', 'contract.product', 'files'])
        ]);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'invoice_date' => 'sometimes|required|date',
            'due_date' => 'sometimes|required|date',
            'amount' => 'sometimes|required|numeric|min:0',
            'payment_status' => 'sometimes|in:pending,paid,late,overdue,cancelled',
            'paid_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        try {
            $oldValues = $transaction->toArray();
            $transaction->update($request->all());

            $this->logActivity('update', $transaction, $oldValues, $transaction->fresh()->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully',
                'data' => $transaction->fresh()->load(['contract', 'files'])
            ]);
        } catch (\Exception $e) {
            $this->logActivity('update', $transaction, $oldValues, $request->all(), 'failed', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadFiles(Request $request, Transaction $transaction)
    {
        $request->validate([
            'files.*' => 'required|file|max:10240',
        ]);

        try {
            $uploadedFiles = [];

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('transaction_files');

                    $transactionFile = TransactionFile::create([
                        'transaction_id' => $transaction->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);

                    $uploadedFiles[] = $transactionFile;
                }
            }

            $this->logActivity('upload_files', $transaction, null, ['files_count' => count($uploadedFiles)]);

            return response()->json([
                'success' => true,
                'message' => 'Files uploaded successfully',
                'data' => $uploadedFiles
            ]);
        } catch (\Exception $e) {
            $this->logActivity('upload_files', $transaction, null, null, 'failed', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload files',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteFile(Transaction $transaction, TransactionFile $file)
    {
        try {
            Storage::delete($file->file_path);
            $file->delete();

            $this->logActivity('delete_file', $transaction, ['file' => $file->toArray()], null);

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

    public function sendNotification(Request $request, Transaction $transaction)
    {
        try {
            $contract = $transaction->contract()->with(['partnerCompany', 'ownCompany', 'product'])->first();

            if (!$contract->partnerCompany->contact_email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Partner company has no contact email'
                ], 400);
            }

            // Email will be implemented in next step
            $transaction->update(['notified_at' => now()]);

            $this->logActivity('send_notification', $transaction, null, ['email' => $contract->partnerCompany->contact_email]);

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully'
            ]);
        } catch (\Exception $e) {
            $this->logActivity('send_notification', $transaction, null, null, 'failed', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Transaction $transaction)
    {
        if (!auth()->user()->can('delete_transactions')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            foreach ($transaction->files as $file) {
                Storage::delete($file->file_path);
                $file->delete();
            }

            $oldValues = $transaction->toArray();
            $transaction->delete();

            $this->logActivity('delete', $transaction, $oldValues, null);

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
