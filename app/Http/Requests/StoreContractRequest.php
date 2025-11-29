<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create_contracts');
    }

    public function rules()
    {
        return [
            'partner_company_id' => 'required|exists:partner_companies,id',
            'own_company_id' => 'required|exists:own_companies,id',
            'product_id' => 'required|exists:products,id',
            'contract_number' => 'nullable|string|max:255|unique:contracts,contract_number',
            'contract_start_date' => 'required|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'payment_type' => 'required|in:one-time,monthly',
            'payment_amount' => 'required|numeric|min:0',
            'status' => 'sometimes|in:active,completed,cancelled,suspended',
            'contract_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'notes' => 'nullable|string',
        ];
    }
}
