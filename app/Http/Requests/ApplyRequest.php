<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApplyRequest extends FormRequest
{
    /**
     * Determina si el usuario tiene permiso para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'candidate';
    }

    /**
     * Define las reglas de validación de la solicitud.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cover_letter' => ['nullable', 'string', 'max:3000'],
            'cv' => CandidateProfileRequest::privatePdfRules(),
        ];
    }
}
