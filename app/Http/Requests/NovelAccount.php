<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NovelAccount extends FormRequest
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

        $rules= [
            'id' => [
                'bail',
                'required',
                Rule::exists('novel')->where(function ($query) {$query->where('deleted_at', null);}),
            ],
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'required' => ':attribute不能为空',
            'unique' => ':attribute已存在',
            'ip' => ':attribute格式不正确',
            'Rule' => ':attribute不存在',
        ];
    }

    public function attributes()
    {
        return [
            'id' => '小说id',
        ];
    }
}
