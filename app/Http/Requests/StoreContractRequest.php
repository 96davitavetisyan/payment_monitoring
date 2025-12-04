<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
//    public function authorize()
//    {
//        return $this->user()->can('create_contracts');
//    }

    public function rules()
    {
        return [
            'partner_company_id' => 'required|exists:partner_companies,id',
            'own_company_id' => 'required|exists:own_companies,id',
            'product_id' => 'required|exists:products,id',
            'contract_number' => 'nullable|string|max:255|unique:contracts,contract_number',
            'contract_start_date' => 'required|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'payment_type' => 'required|in:one_time,monthly,yearly',
            'account_number' => 'required|numeric|min:0',
            'payment_amount' => 'required|numeric|min:0',
            'status' => 'sometimes|in:active,completed,cancelled,suspended',
            'contract_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'notes' => 'nullable|string',

            'payment_date' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) {
                    if (in_array($this->payment_type, ['monthly', 'yearly']) && !$value) {
                        $fail('Վճարման օրը պարտադիր է՝ ընտրելով «Տարեկան» կամ «Ամսական» վճարումը։');
                    }
                },
            ],
            'payment_finish_date' => [
                'nullable',
                'numeric',
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
            'payment_type.in' => 'Վճարման տեսակը պետք է լինի միանվագ, ամսական կամ տարեկան',
            'account_number.required' => 'Հաշվի համարը պարտադիր է',
            'account_number.min' => 'Հաշվի համարը պետք է լինի 0-ից մեծ կամ հավասար',
            'payment_amount.required' => 'Վճարման գումարը պարտադիր է',
            'payment_amount.min' => 'Վճարման գումարը պետք է լինի 0-ից մեծ կամ հավասար',
            'contract_file.mimes' => 'Պայմանագրի ֆայլը պետք է լինի PDF, DOC կամ DOCX ձևաչափով',
            'contract_file.max' => 'Պայմանագրի ֆայլի չափը չպետք է գերազանցի 10ՄԲ',
        ];
    }
}
