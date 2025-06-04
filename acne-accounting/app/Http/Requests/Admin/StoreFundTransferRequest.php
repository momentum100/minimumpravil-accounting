<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Account;
use App\Rules\AccountBelongsToUser; // Custom rule needed
use Illuminate\Validation\Rule;

class StoreFundTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ensure the logged-in user is an admin (owner/finance)
        return $this->user() && in_array($this->user()->role, ['owner', 'finance']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'from_account_id' => [
                'required', 
                'integer', 
                Rule::exists('accounts', 'id')->where('currency', 'USD'), // Ensure it's a USD account
                new AccountBelongsToUser($this->input('from_user_id')) // Custom rule check
            ],
            'to_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'to_account_id' => [
                'required', 
                'integer', 
                Rule::exists('accounts', 'id')->where('currency', 'USD'), // Ensure it's a USD account
                new AccountBelongsToUser($this->input('to_user_id')), // Custom rule check
                'different:from_account_id' // Cannot be the same as the from account
            ],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'], // Positive amount, max 2 decimal places
            'description' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'from_account_id.exists' => 'The selected source account is invalid or not a USD account.',
            'to_account_id.exists' => 'The selected destination account is invalid or not a USD account.',
            'to_account_id.different' => 'The destination account must be different from the source account.',
            'amount.gt' => 'The amount must be greater than zero.',
            'amount.decimal' => 'The amount must not have more than 2 decimal places.',
        ];
    }
} 