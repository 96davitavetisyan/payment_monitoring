<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReminderMail;
use App\Models\Transaction;
use App\Models\File;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
            'invoice_number' => 'required|numeric',
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
                    $path = $file->store('transaction_files', 'public');

                    $transaction->files()->create([
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
                    $path = $file->store('transaction_files', 'public');

                    $transactionFile = $transaction->files()->create([
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

    public function deleteFile(Transaction $transaction, File $file)
    {
        try {
            // Check if file belongs to this transaction
            if ($file->fileable_id != $transaction->id || $file->fileable_type != 'App\Models\Transaction') {
                return response()->json([
                    'success' => false,
                    'message' => 'File does not belong to this transaction'
                ], 403);
            }

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
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        try {
            $contract = $transaction->contract()->with(['partnerCompany'])->first();

            if (!$contract->partnerCompany->contact_email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Partner company has no contact email'
                ], 400);
            }

            $file = $request->file('file');
            $path = $file->store('transaction_files', 'public');

            $transactionFile = $transaction->files()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            Mail::send('emails.payment_reminder', ['transaction' => $transaction], function ($message) use ($contract, $transactionFile) {
                $message->to($contract->partnerCompany->contact_email)
                    ->subject('Վճարման հիշեցում')
                    ->attach(storage_path('app/' . $transactionFile->file_path), [
                        'as' => $transactionFile->file_name,
                        'mime' => $transactionFile->file_type
                    ]);
            });

//            Mail::raw('Test', function($message){
//                $message->to('test@example.com')->subject('Test email');
//            });

            $transaction->update(['notified_at' => now(),'payment_status'=>'notified']);

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Transaction $transaction)
    {
//        if (!auth()->user()->can('delete_transactions')) {
//            return response()->json(['message' => 'Unauthorized'], 403);
//        }

        try {
            $transaction->delete();
            $this->logActivity('soft_delete', $transaction, $transaction->toArray(), null);

            return response()->json([
                'success' => true,
                'message' => 'Transaction soft deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updatePaidFile(Request $request, Transaction $transaction)
    {
        $request->validate([
            'paid_date' => 'required|date',
            'file' => 'required|file|max:10240', // 10MB
        ]);

        try {

            $path = $request->file('file')->store('transaction_files', 'public');

            $transaction->files()->create([
                'file_path' => $path,
                'file_name' => $request->file('file')->getClientOriginalName(),
                'file_type' => $request->file('file')->getMimeType(),
                'file_size' => $request->file('file')->getSize(),
            ]);

            $transaction->update([
                'paid_date' => $request->paid_date,
                'payment_status' => 'paid',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully',
                'data' => $transaction->load('files')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function downloadFile(File $file)
    {
        if (!$file->file_path || !Storage::exists($file->file_path)) {
            abort(404, 'File not found');
        }
        return Storage::download($file->file_path, $file->file_name ?? 'file');
    }
}
