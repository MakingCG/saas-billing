<?php

namespace VueFileManager\Subscription\Domain\BillingAlerts\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBillingAlertRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric',
        ];
    }
}
