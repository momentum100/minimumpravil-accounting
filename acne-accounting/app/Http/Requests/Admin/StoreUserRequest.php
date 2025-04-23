<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Assuming admin users can create other users
        return true; // Add specific role check $this->user()->isAdmin() if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'confirmed', Password::defaults()], // Require confirmation, nullable for users without web login
            'telegram_id' => ['nullable', 'numeric', 'unique:users,telegram_id'],
            'role' => ['required', Rule::in(['owner', 'finance', 'buyer', 'agency'])],
            'active' => ['required', 'boolean'],

            // Buyer specific
            'team_id' => ['nullable', 'required_if:role,buyer', Rule::exists('teams', 'id')],
            'sub2' => ['nullable', 'required_if:role,buyer', 'json'], // Validate as JSON string

            // Agency specific
            'contact_info' => ['nullable', 'required_if:role,agency', 'string', 'max:1000'],

            // is_virtual is set automatically in the controller based on role
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
            'team_id.required_if' => 'The team field is required when role is Buyer.',
            'sub2.required_if' => 'The sub2 field is required when role is Buyer.',
            'contact_info.required_if' => 'The contact info field is required when role is Agency.',
            'email.unique' => 'This email address is already in use.',
            'telegram_id.unique' => 'This Telegram ID is already in use.',
        ];
    }
} 