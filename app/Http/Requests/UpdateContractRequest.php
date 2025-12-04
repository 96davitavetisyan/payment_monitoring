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

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'partner_company_id.required' => 'Գործընկեր ընկերությունը պարտադիր է',
            'partner_company_id.exists' => 'Ընտրված գործընկեր ընկերությունը գոյություն չունի',
            'own_company_id.required' => 'Սեփական ընկերությունը պարտադիր է',
            'own_company_id.exists' => 'Ընտրված սեփական ընկերությունը գոյություն չունի',
            'product_id.required' => 'Ապրանքը պարտադիր է',
            'product_id.exists' => 'Ընտրված ապրանքը գոյություն չունի',
            'contract_number.unique' => 'Այս համարով պայմանագիրը արդեն գոյություն ունի',
            'contract_start_date.required' => 'Պայմանագրի սկզբի ամսաթիվը պարտադիր է',
            'contract_end_date.after' => 'Պայմանագրի ավարտի ամսաթիվը պետք է լինի սկզբի ամսաթվից հետո',
            'payment_type.required' => 'Վճարման տեսակը պարտադիր է',
            'payment_type.in' => 'Վճարման տեսակը պետք է լինի տարեկան, ամսական կամ միանվագ',
            'account_number.required' => 'Հաշվի համարը պարտադիր է',
            'account_number.min' => 'Հաշվի համարը պետք է լինի 0-ից մեծ կամ հավասար',
            'payment_amount.required' => 'Վճարման գումարը պարտադիր է',
            'payment_amount.min' => 'Վճարման գումարը պետք է լինի 0-ից մեծ կամ հավասար',
            'payment_date.min' => 'Վճարման օրը պետք է լինի 1-ից մեծ կամ հավասար',
            'payment_date.max' => 'Վճարման օրը պետք է լինի 31-ից փոքր կամ հավասար',
            'payment_finish_date.min' => 'Վճարման վերջնական օրը պետք է լինի 1-ից մեծ կամ հավասար',
            'payment_finish_date.max' => 'Վճարման վերջնական օրը պետք է լինի 31-ից փոքր կամ հավասար',
            'contract_file.mimes' => 'Պայմանագրի ֆայլը պետք է լինի PDF, DOC կամ DOCX ձևաչափով',
            'contract_file.max' => 'Պայմանագրի ֆայլի չափը չպետք է գերազանցի 10ՄԲ',
        ];
    }
}
