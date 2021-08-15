<?php

namespace Domain\Plans\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
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
        return [
            'name'        => 'required|string',
            'price'       => 'sometimes|integer',
            'storage'     => 'sometimes|integer|nullable',
            'interval'    => 'required|string',
            'description' => 'sometimes|string|nullable',
        ];
    }
}
