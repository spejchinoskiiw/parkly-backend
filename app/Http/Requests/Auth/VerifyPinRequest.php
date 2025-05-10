<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class VerifyPinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'regex:/^[^@]+@iwconnect\.com$/'],
            'pin' => ['required', 'string', 'size:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.regex' => 'Only @iwconnect.com email addresses are allowed.',
            'pin.size' => 'The PIN must be exactly 6 digits.',
        ];
    }
} 