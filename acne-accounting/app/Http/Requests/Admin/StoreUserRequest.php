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
        Log::info('StoreUserRequest: Role check for rules', ['role' => $this->input('role'), 'isAgency' => $isAgency]);
        $availableRoles = ['owner', 'finance', 'buyer', 'agency']; // Define available roles

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in($availableRoles)], // Validate role
            'is_virtual' => ['sometimes', 'boolean'],
            'telegram_id' => ['nullable', 'numeric', // Use telegram_id now
                Rule::requiredIf(!$isAgency),
            ],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email',
                Rule::requiredIf(!$isAgency),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults(),
                Rule::requiredIf(!$isAgency),
            ],
            'sub2' => ['nullable', 'array'], // Validate 'sub2' field
            'sub2.*' => ['string', 'max:50'], // Validate individual tags in sub2
            'terms' => ['nullable', 'numeric', 'min:0', 'max:1',
                Rule::requiredIf($isAgency),
            ],
            // Add validation for other fields if needed (team_id, contact_info, active)
            'team_id' => ['nullable', 'exists:teams,id', Rule::requiredIf($this->input('role') === 'buyer')], // Required only for buyer
            'contact_info' => ['nullable', 'string', Rule::requiredIf($isAgency)], // Required only for agency
            'active' => ['sometimes', 'boolean'],
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

        $sub2Input = $this->input('sub2');
        $sub2Array = null;

        if ($isBuyer && is_string($sub2Input)) {
            Log::info('StoreUserRequest: Processing sub2 textarea for buyer');
            $sub2Array = collect(explode("\n", $sub2Input))
                ->map(fn ($line) => trim($line))
                ->filter()
                ->values()
                ->all();
        }

        // Ensure boolean fields have defaults / correct types
        $this->merge([
            'is_virtual' => $this->boolean('is_virtual'),
            'active' => $this->boolean('active'), // Ensure active is boolean
            // Overwrite sub2 with processed array for buyers, or null for others
            'sub2' => $sub2Array, 
        ]);

        // Null out fields based on role
        if ($isAgency) {
            $this->merge([
                'email' => null,
                'password' => null,
                'password_confirmation' => null,
                'telegram_id' => null,
                'team_id' => null, // Agencies might not have teams
            ]);
        } else {
            // If not an agency, null out agency-specific fields
            $this->merge([
                'terms' => null,
                // Null contact_info only if it's not a buyer (buyers might have it?)
                // Let validation handle requirement based on role
                // 'contact_info' => null, 
            ]);
            // Null out buyer specific fields if role is not buyer
            if (!$isBuyer) {
                $this->merge(['team_id' => null]);
            }
        }
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