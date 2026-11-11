<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    /**
     * Determina si el usuario tiene permiso para realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Define las reglas de validación de la solicitud.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:100'],
            'details' => ['required', 'string', 'min:10', 'max:3000'],
        ];
    }
}
