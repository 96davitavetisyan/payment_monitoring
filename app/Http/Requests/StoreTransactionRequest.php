<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create_transactions');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'company_name' => 'required|string|max:255',
            'person_name' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'max_overdue_date' => 'nullable|date|after_or_equal:transaction_date',
            'amount' => 'required|numeric|min:0',
            'payment_status' => 'required|in:paid,unpaid,late,overdue,notified',
            'transaction_type' => 'required|in:one-time,monthly',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after_or_equal:contract_start_date',
            'is_active' => 'boolean',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'contract_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'company_name.required' => 'Company name is required',
            'transaction_date.required' => 'Transaction date is required',
            'amount.required' => 'Amount is required',
            'amount.min' => 'Amount must be greater than or equal to 0',
            'payment_status.required' => 'Payment status is required',
            'transaction_type.required' => 'Transaction type is required',
            'contract_end_date.after_or_equal' => 'Contract end date must be after or equal to start date',
            'max_overdue_date.after_or_equal' => 'Max overdue date must be after or equal to transaction date',
        ];
    }
}
