<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Account;

class AccountBelongsToUser implements ValidationRule
{
    protected $userId;

    /**
     * Create a new rule instance.
     *
     * @param int|null $userId The ID of the user the account must belong to.
     * @return void
     */
    public function __construct(?int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($this->userId) || empty($value)) {
            // Cannot validate if user ID or account ID is missing
            // Other rules (required, exists) should catch this.
            return;
        }

        $account = Account::find($value); // $value is the account_id being validated

        if (!$account || $account->user_id !== $this->userId) {
            $fail("The selected :attribute does not belong to the specified user.");
        }
    }
}
