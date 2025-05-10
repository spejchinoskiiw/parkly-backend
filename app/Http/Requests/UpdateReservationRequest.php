<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization moved to controller to handle role-based access
        // Using the ReservationPolicy
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_time' => ['sometimes', 'date', 'after_or_equal:now'],
            'end_time' => ['sometimes', 'date', 'after:start_time'],
        ];
    }

    /**
     * Get custom validation error messages.
     * 
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_time.after_or_equal' => 'The start time must be a future date and time.',
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }
}
