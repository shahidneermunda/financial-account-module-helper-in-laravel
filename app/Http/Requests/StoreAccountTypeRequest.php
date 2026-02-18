<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $accountTypeId = $this->route('account_type')?->id;
        $accountType = $accountTypeId ? \App\Models\AccountType::find($accountTypeId) : null;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                'uppercase',
                Rule::unique('account_types', 'code')->ignore($accountTypeId),
            ],
            'normal_balance' => ['required', 'string', Rule::in(['DEBIT', 'CREDIT'])],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_system' => ['nullable', 'boolean'],
        ];

        // Prevent changing critical fields for system account types
        if ($accountType && $accountType->is_system) {
            if ($this->has('code') && $this->code !== $accountType->code) {
                $rules['code'] = ['prohibited'];
            }
            if ($this->has('name') && $this->name !== $accountType->name) {
                $rules['name'] = ['prohibited'];
            }
            if ($this->has('normal_balance') && $this->normal_balance !== $accountType->normal_balance) {
                $rules['normal_balance'] = ['prohibited'];
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'An account type with this code already exists.',
            'normal_balance.in' => 'Normal balance must be either DEBIT or CREDIT.',
        ];
    }
}
