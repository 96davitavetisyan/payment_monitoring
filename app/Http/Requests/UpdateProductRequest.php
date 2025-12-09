<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
//    public function authorize()
//    {
//        return $this->user()->can('edit_products');
//    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $product = $this->route('product');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->ignore($product->id),
            ],
            'start_date' => 'nullable|date',
            'status' => 'required|in:active,suspended',
            'own_company_id' => 'nullable|exists:own_companies,id',
            'type' => 'required|in:local,international',
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
            'name.required' => 'Պրոդուկտի անունը պարտադիր է',
            'name.unique' => 'Այս անունով պրոդուկտ արդեն գոյություն ունի',
            'status.required' => 'Պրոդուկտի կարգավիճակը պարտադիր է',
        ];
    }
}
