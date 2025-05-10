<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'regex:/^[^@]+@iwconnect\.com$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.regex' => 'Only @iwconnect.com email addresses are allowed.',
        ];
    }
} 