<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTitleSemantics implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check for forbidden words inside the value
        if (str_contains(strtolower($value), 'forbidden-word')) {
            $fail("The {$attribute} contains unapproved corporate terminology.");
        }
    }
}