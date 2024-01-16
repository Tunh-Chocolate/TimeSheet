<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateStaffRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name'                  => 'required|max:225',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|confirmed',
            'code'    => 'required|unique:users,code'

        ];

        return $rules;
    }
}
