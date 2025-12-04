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
            'responsible_user_id' => 'required|exists:users,id',
            'status' => 'required|in:active,suspended',
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
            'name.required' => 'Ապրանքի անունը պարտադիր է',
            'name.unique' => 'Այս անունով ապրանք արդեն գոյություն ունի',
            'responsible_user_id.required' => 'Պատասխանատու օգտատերը պարտադիր է',
            'responsible_user_id.exists' => 'Ընտրված օգտատերը գոյություն չունի',
            'status.required' => 'Ապրանքի կարգավիճակը պարտադիր է',
        ];
    }
}
