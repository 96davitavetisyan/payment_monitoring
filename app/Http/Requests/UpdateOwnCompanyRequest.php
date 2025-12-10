<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOwnCompanyRequest extends FormRequest
{
//    public function authorize()
//    {
//        return $this->user()->can('edit_own_companies');
//    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'tax_id' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'requisites' => 'nullable|string',
            'is_active' => 'nullable',
            'files.*' => 'nullable|file|max:10240',
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
            'name.required' => 'Ընկերության անունը պարտադիր է',
            'name.string' => 'Անունը պետք է լինի տեքստային',
            'name.max' => 'Անունը չի կարող գերազանցել 255 նիշը',
            'tax_id.required' => 'ՀՎՀՀ-ն պարտադիր է',
            'tax_id.max' => 'ՀԴՄ/ՀՎՀՀ-ն չի կարող գերազանցել 255 նիշը',
            'address.required' => 'Հասցեն պարտադիր է',
            'address.string' => 'Հասցեն պետք է լինի տեքստային',
            'address.max' => 'Հասցեն չի կարող գերազանցել 500 նիշը',
            'phone.required' => 'Հեռախոսահամարը պարտադիր է',
            'email.required' => 'Էլ․ Փոստը պարտադիր է',
            'is_active.boolean' => 'Ակտիվության դաշտը պետք է լինի True կամ False',
            'files.*.file' => 'Յուրաքանչյուր ֆայլ պետք է լինի վավեր',
            'files.*.max' => 'Յուրաքանչյուր ֆայլ չպետք է գերազանցի 10MB',
        ];
    }
}
