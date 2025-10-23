<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreWaterLevelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow IoT devices to send data
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'device_id' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9_-]+$/', // Allow alphanumeric, underscore, and dash
            ],
            'level_cm' => [
                'required',
                'numeric',
                'min:0',
                'max:1000', // Reasonable max water level
            ],
            'timestamp' => [
                'sometimes',
                'date',
            ],
            'battery_level' => [
                'sometimes',
                'numeric',
                'min:0',
                'max:100',
            ],
            'temperature' => [
                'sometimes',
                'numeric',
                'min:-40',
                'max:85',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'device_id.required' => 'Device ID is required.',
            'device_id.regex' => 'Device ID can only contain letters, numbers, underscores, and dashes.',
            'level_cm.required' => 'Water level measurement is required.',
            'level_cm.numeric' => 'Water level must be a number.',
            'level_cm.min' => 'Water level cannot be negative.',
            'level_cm.max' => 'Water level cannot exceed 1000cm.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
