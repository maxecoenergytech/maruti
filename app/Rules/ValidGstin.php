<?php

namespace App\Rules;

use App\Support\GstHelper;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidGstin implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        if (!GstHelper::isValidGstin($value)) {
            $fail('The :attribute must be a valid 15-character Indian GSTIN with an authorized state code.');
        }
    }
}
