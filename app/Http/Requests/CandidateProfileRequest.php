<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class CandidateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'candidate';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'phone' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+() .-]+$/'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'experience' => ['nullable', 'string', 'max:5000'],
            'cv' => self::privatePdfRules(),
        ];
    }

    /** @return array<int, mixed> */
    public static function privatePdfRules(): array
    {
        return ['bail', 'nullable', 'file', 'mimes:pdf', 'extensions:pdf', 'max:5120', function (string $attribute, mixed $value, Closure $fail): void {
            if (! $value instanceof UploadedFile) {
                return;
            }

            $stream = fopen($value->getRealPath(), 'rb');
            $header = $stream ? fread($stream, 5) : false;
            if ($stream) {
                fclose($stream);
            }

            if ($header !== '%PDF-') {
                $fail('El archivo debe ser un documento PDF válido.');
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
    }
}
