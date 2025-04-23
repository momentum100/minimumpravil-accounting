<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add specific role check $this->user()->isAdmin() if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
             'name' => ['required', 'string', 'max:255'],
             'email' => [
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
             ],
             'password' => ['nullable', 'confirmed', Password::defaults()], // Only validate if present
             'telegram_id' => [
                'nullable',
                'numeric',
                 Rule::unique('users', 'telegram_id')->ignore($userId),
             ],
             'role' => ['required', Rule::in(['owner', 'finance', 'buyer', 'agency'])],
             'active' => ['required', 'boolean'],

             // Buyer specific
             'team_id' => ['nullable', 'required_if:role,buyer', Rule::exists('teams', 'id')],
             'sub2' => ['nullable', 'required_if:role,buyer', 'json'], // Validate as JSON string

             // Agency specific
             'contact_info' => ['nullable', 'required_if:role,agency', 'string', 'max:1000'],
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