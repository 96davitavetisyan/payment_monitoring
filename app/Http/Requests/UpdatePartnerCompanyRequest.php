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
            'name' => 'sometimes|required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
