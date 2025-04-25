<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\User; // Import User model if needed for validation rules
use Illuminate\Support\Facades\Log; // Import Log facade

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Log::info('StoreUserRequest: Checking authorization...');
        $isAuthorized = auth()->check(); // Allow any authenticated user for now
        Log::info('StoreUserRequest: Authorization result: ' . ($isAuthorized ? 'true' : 'false'));
        return $isAuthorized;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        Log::info('StoreUserRequest: Generating validation rules...', ['request_data' => $this->all()]);
        $isAgency = $this->input('role') === 'agency';
        $isBuyer = $this->input('role') === 'buyer';
        // Define roles needing web login credentials
        $isWebLoginRole = in_array($this->input('role'), ['owner', 'finance', 'buyer']); 
        Log::info('StoreUserRequest: Role check for rules', ['role' => $this->input('role'), 'isAgency' => $isAgency, 'isBuyer' => $isBuyer, 'isWebLoginRole' => $isWebLoginRole]);
        $availableRoles = ['owner', 'finance', 'buyer', 'agency']; // Define available roles

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in($availableRoles)],
            // is_virtual is set in prepareForValidation, no direct input validation needed
            'telegram_id' => ['nullable', 'numeric', 'unique:users,telegram_id'], // Optional for all, unique if provided
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email', 
                Rule::requiredIf($isWebLoginRole), // Required only for roles needing web login
            ],
            'password' => ['nullable', 'confirmed', Password::defaults(),
                Rule::requiredIf($isWebLoginRole), // Required only if email/web login is needed
            ],
            'sub2' => ['nullable', 'array', Rule::requiredIf($isBuyer)], // Required only for buyer
            'sub2.*' => ['string', 'max:50'],
            'terms' => ['nullable', 'numeric', 'min:0', // Terms: 0 to 1 (e.g., 0.01 for 1%)
                Rule::requiredIf($isAgency), // Required only for agency
            ],
            'team_id' => ['nullable', 'exists:teams,id', Rule::requiredIf($isBuyer)], // Required only for buyer
            'contact_info' => ['nullable', 'string', 'max:1000', Rule::requiredIf($isAgency)], // Required only for agency
            'active' => ['sometimes', 'boolean'], // Handled in prepareForValidation default
        ];

        Log::info('StoreUserRequest: Finished generating rules.');
        return $rules;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        Log::info('StoreUserRequest: Before prepareForValidation', ['request_data' => $this->all()]);
        $role = $this->input('role');
        $isAgency = $role === 'agency';
        $isBuyer = $role === 'buyer';
        $isWebLoginRole = in_array($role, ['owner', 'finance', 'buyer']); // Define roles needing web login

        $sub2Input = $this->input('sub2');
        $sub2Array = null;

        // Process sub2 textarea for buyers
        if ($isBuyer && is_string($sub2Input)) {
            Log::info('StoreUserRequest: Processing sub2 textarea for buyer');
            $sub2Array = collect(explode("\n", $sub2Input))
                ->map(fn ($line) => trim($line))
                ->filter() // Remove empty lines
                ->values()
                ->all();
        }

        $mergeData = [
            // Ensure active defaults to true if not present, and is boolean
            'active' => $this->boolean('active', true),
            // Set is_virtual based on role
            'is_virtual' => $isAgency,
            // Use processed sub2 array for buyers, null otherwise
            'sub2' => $sub2Array,
        ];

        // Null out fields based on role
        if ($isAgency) {
             // Null out fields not applicable to agencies
            $mergeData['team_id'] = null;
            $mergeData['sub2'] = null; // Ensure sub2 is null for agencies
            // Keep telegram_id, contact_info, terms as they are relevant for agencies
        } else {
            // If not an agency, null out agency-specific fields
            $mergeData['terms'] = null;
            $mergeData['contact_info'] = null; // Contact info is only for Agency role

            // If not a buyer, null out buyer-specific fields
            if (!$isBuyer) {
                $mergeData['team_id'] = null;
                $mergeData['sub2'] = null; // Ensure sub2 is null if not buyer
            }
        }

        // If not a role needing web login (i.e., only agency now), null out email/password
        if (!$isWebLoginRole) {
             $mergeData['email'] = null;
             $mergeData['password'] = null;
             $mergeData['password_confirmation'] = null;
        }

        $this->merge($mergeData);

        Log::info('StoreUserRequest: After prepareForValidation', ['request_data' => $this->all()]);
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