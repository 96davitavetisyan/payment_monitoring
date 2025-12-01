<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContractRequest extends FormRequest
{
//    public function authorize()
//    {
//        return $this->user()->can('edit_contracts');
//    }
    protected function prepareForValidation()
    {
        if ($this->payment_type === 'one_time') {
            $this->merge([
                'payment_date' => null,
                'payment_finish_date' => null,
            ]);
        } else {
            $this->merge([
                'payment_date' => $this->payment_date ?: null,
                'payment_finish_date' => $this->payment_finish_date ?: null,
            ]);
        }
    }
    public function rules()
    {
        return [
            'partner_company_id' => 'sometimes|required|exists:partner_companies,id',
            'own_company_id' => 'sometimes|required|exists:own_companies,id',
            'product_id' => 'sometimes|required|exists:products,id',
            'contract_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('contracts', 'contract_number')->ignore($this->route('contract')),
            ],
            'contract_start_date' => 'sometimes|required|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'payment_type' => 'sometimes|required|in:yearly,monthly,one_time',
            'payment_amount' => 'sometimes|required|numeric|min:0',
            'account_number' => 'required|numeric|min:0',
            'status' => 'sometimes|in:active,completed,cancelled,suspended',
            'contract_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'notes' => 'nullable|string',

            'payment_date' => [
                'nullable',
                'numeric',
                'min:1',
                'max:31',
                function ($attribute, $value, $fail) {
                    if (in_array($this->payment_type, ['monthly', 'yearly']) && !$value) {
                        $fail('Վճարման օրը պարտադիր է՝ ընտրելով «Տարեկան» կամ «Ամսական» վճարումը։');
                    }
                },
            ],

            'payment_finish_date' => [
                'nullable',
                'numeric',
                'min:1',
                'max:31',
                function ($attribute, $value, $fail) {
                    if (in_array($this->payment_type, ['monthly', 'yearly']) && !$value) {
                        $fail('Վճարման վերջնական օրը պարտադիր է՝ ընտրելով «Տարեկան» կամ «Ամսական» վճարումը։');
                    }
                },
            ],
        ];
    }
}
