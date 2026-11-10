<?php

namespace App\Http\Requests;

use App\Models\JobOffer;
use App\Services\LocationSearch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->active && in_array($this->user()->role, ['employer', 'admin'], true);
    }

    protected function prepareForValidation(): void
    {
        if ($this->hasAny(['location_name', 'location_selection', 'work_mode', 'latitude', 'longitude'])) {
            $this->merge(app(LocationSearch::class)->offerAttributes($this->all()));
        }
        if ($this->input('intent') === 'publish') {
            $this->merge(['status' => 'published']);
        } elseif ($this->input('intent') === 'preview') {
            $job = $this->route('job');
            if (! $job instanceof JobOffer || $job->status === 'draft') {
                $this->merge(['status' => 'draft']);
            }
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('active', true)],
            'district_id' => ['nullable', Rule::exists('districts', 'id')->where('active', true)],
            'description' => ['required', 'string', 'min:30', 'max:10000'],
            'requirements' => ['required', 'string', 'min:10', 'max:6000'],
            'conditions' => ['required', 'string', 'min:10', 'max:6000'],
            'schedule' => ['required', Rule::in(['full_time', 'part_time', 'shifts'])],
            'contract_type' => ['required', Rule::in(['permanent', 'temporary', 'internship'])],
            'salary_min' => ['required', 'numeric', 'min:0', 'max:999999'],
            'salary_max' => ['nullable', 'numeric', 'gte:salary_min', 'max:999999'],
            'vacancies' => ['required', 'integer', 'min:1', 'max:1000'],
            'cv_required' => ['required', 'boolean'],
            'work_mode' => ['sometimes', 'required', Rule::in(array_keys(JobOffer::WORK_MODES))],
            'location_name' => ['nullable', Rule::requiredIf(fn () => $this->input('work_mode', 'onsite') !== 'remote' && ! $this->filled('district_id')), 'string', 'max:255'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-19,1'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-84,-68'],
            'expires_at' => ['required', 'date', Rule::when($this->input('status') === 'published', 'after_or_equal:today')],
            'status' => ['required', Rule::in(['draft', 'published', 'closed'])],
            'intent' => ['sometimes', Rule::in(['save', 'preview', 'publish'])],
        ];
    }

    public function attributes(): array
    {
        return ['title' => 'puesto', 'description' => 'descripción', 'requirements' => 'requisitos', 'conditions' => 'condiciones', 'salary_min' => 'salario mínimo', 'salary_max' => 'salario máximo', 'expires_at' => 'fecha de cierre'];
    }
}
