<?php

namespace App\Models;

use Database\Factories\JobOfferFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOffer extends Model
{
    protected $fillable = ['company_id', 'category_id', 'district_id', 'title', 'slug', 'description', 'requirements', 'conditions', 'schedule', 'contract_type', 'salary_min', 'salary_max', 'vacancies', 'expires_at', 'status', 'featured', 'is_demo', 'cv_required', 'location_name', 'latitude', 'longitude', 'work_mode'];

    protected $attributes = ['cv_required' => true, 'work_mode' => 'onsite'];

    /** @use HasFactory<JobOfferFactory> */
    use HasFactory;

    public const SCHEDULES = ['full_time' => 'Tiempo completo', 'part_time' => 'Medio tiempo', 'shifts' => 'Por turnos'];

    public const CONTRACT_TYPES = ['permanent' => 'Indeterminado', 'temporary' => 'Temporal', 'internship' => 'Prácticas'];

    public const STATUSES = ['draft' => 'Borrador', 'published' => 'Publicada', 'closed' => 'Cerrada'];

    public const WORK_MODES = ['onsite' => 'Presencial', 'hybrid' => 'Híbrido', 'remote' => 'Remoto'];

    public function getLocationLabelAttribute(): string
    {
        if ($this->work_mode === 'remote') {
            return 'Remoto'.($this->location_name ? ' · '.$this->location_name : '');
        }

        return $this->location_name ?: ($this->district ? $this->district->name.', Cusco' : 'Ubicación por confirmar');
    }

    public function hasCoordinates(): bool
    {
        return $this->work_mode !== 'remote' && $this->latitude !== null && $this->longitude !== null
            && $this->latitude >= -19 && $this->latitude <= 1 && $this->longitude >= -84 && $this->longitude <= -68;
    }

    public function scopeLocated(Builder $query): void
    {
        $query->where('work_mode', '!=', 'remote')->whereBetween('latitude', [-19, 1])->whereBetween('longitude', [-84, -68]);
    }

    public static function distanceExpression(float $latitude, float $longitude): array
    {
        $dot = '(COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))';

        return ["6371 * ACOS(CASE WHEN $dot > 1 THEN 1 WHEN $dot < -1 THEN -1 ELSE $dot END)",
            array_merge(...array_fill(0, 3, [$latitude, $longitude, $latitude]))];
    }

    public function scopeWithDistanceFrom(Builder $query, float $latitude, float $longitude): void
    {
        [$sql, $bindings] = static::distanceExpression($latitude, $longitude);
        if ($query->getQuery()->columns === null) {
            $query->select('job_offers.*');
        }
        $query->selectRaw("CASE WHEN work_mode != 'remote' AND latitude BETWEEN -19 AND 1 AND longitude BETWEEN -84 AND -68 THEN $sql END AS distance_km", $bindings);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function savedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')
            ->where(fn (Builder $query) => $query->whereNull('expires_at')->orWhereDate('expires_at', '>=', today()))
            ->whereHas('company.user', fn (Builder $query) => $query->where('active', true)->where('role', 'employer'))
            ->whereHas('category', fn (Builder $query) => $query->where('active', true))
            ->where(fn (Builder $query) => $query->whereNull('district_id')->orWhereHas('district', fn (Builder $districts) => $districts->where('active', true)));
    }

    public function scopeWithSavedState(Builder $query, ?User $user): void
    {
        if ($user?->active && $user->role === 'candidate') {
            $query->withExists(['savedBy as is_saved' => fn (Builder $savedBy) => $savedBy->where('users.id', $user->id)]);
        }
    }

    /** @param array<string, mixed> $filters */
    public function scopeFilter(Builder $query, array $filters): void
    {
        if (! empty($filters['q'])) {
            $search = '%'.trim($filters['q']).'%';
            $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', $search)
                    ->orWhere('description', 'like', $search)
                    ->orWhereHas('company', fn (Builder $query) => $query->where('name', 'like', $search));
            });
        }

        foreach (['district' => 'district_id', 'category' => 'category_id', 'schedule' => 'schedule', 'contract_type' => 'contract_type'] as $key => $column) {
            if (! empty($filters[$key])) {
                $query->where($column, $filters[$key]);
            }
        }

        if (isset($filters['salary_min']) && $filters['salary_min'] !== '') {
            $query->whereRaw('COALESCE(salary_max, salary_min) >= CAST(? AS DECIMAL(10, 2))', [(float) $filters['salary_min']]);
        }

        if (! empty($filters['verified'])) {
            $query->whereHas('company', fn (Builder $query) => $query->where('verified', true));
        }

        if (($filters['sort'] ?? '') === 'distance' && ($filters['_has_origin'] ?? false)) {
            $query->orderByRaw('distance_km IS NULL')->orderBy('distance_km');
        } elseif (($filters['sort'] ?? 'recent') === 'salary') {
            $query->orderByDesc('salary_min');
        } else {
            $query->latest();
        }

        $query->orderByDesc('id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'published'
            && ($this->expires_at === null || $this->expires_at->copy()->endOfDay()->isFuture())
            && $this->company->user->active
            && $this->company->user->role === 'employer'
            && $this->category->active
            && ($this->district_id === null || $this->district?->active);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return ['salary_min' => 'decimal:2', 'salary_max' => 'decimal:2', 'expires_at' => 'date', 'featured' => 'boolean', 'is_demo' => 'boolean', 'cv_required' => 'boolean', 'latitude' => 'float', 'longitude' => 'float'];
    }
}
