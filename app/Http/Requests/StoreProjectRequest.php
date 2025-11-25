<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create_projects');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:projects,name',
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
            'name.required' => 'Project name is required',
            'name.unique' => 'A project with this name already exists',
            'responsible_user_id.required' => 'Responsible user is required',
            'responsible_user_id.exists' => 'Selected user does not exist',
            'status.required' => 'Project status is required',
        ];
    }
}
