<?php

declare(strict_types=1);

namespace App\Http\Requests\ParkingSpot;

use App\Models\Facility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class StoreParkingSpotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $facility = Facility::findOrFail($this->input('facility_id'));
        return Gate::allows('create-parking-spot', $facility);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'facility_id' => ['required', 'integer', 'exists:facilities,id'],
            'spot_number' => ['required', 'integer', 'min:1'],
        ];
    }
}
