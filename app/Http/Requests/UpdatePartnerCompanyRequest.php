<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerCompanyRequest extends FormRequest
{
//    public function authorize()
//    {
////        return $this->user()->can('edit_partner_companies');
//    }

    public function rules()
    {
        return [
            'type' => 'sometimes|required|in:merchant,international',
            'name' => 'sometimes|required|string|max:255',
            'tax_id' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_person_position' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:255',
            'is_active' => 'boolean',
            'employees' => 'nullable|array',
            'employees.*.name' => 'required|string|max:255',
            'employees.*.position' => 'nullable|string|max:255',
            'employees.*.email' => 'nullable|email|max:255',
            'employees.*.phone' => 'nullable|string|max:255',
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
            'name.required' => 'Կազմակերպության անվանումը պարտադիր է',
            'tax_id.required' => 'ՀՎՀՀ-ն պարտադիր է',
            'contact_person.required' => 'Կոնտակտային անձը պարտադիր է',
            'contact_person_position.required' => 'Կոնտակտային անձի պաշտոնը պարտադիր է',
            'contact_email.required' => 'Էլ․ փոստի հասցեն պարտադիր է',
            'contact_phone.required' => 'Հեռախոսահամարը պարտադիր է',
        ];
    }
}
