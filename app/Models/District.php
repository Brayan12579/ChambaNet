<?php

namespace App\Models;

use Database\Factories\DistrictFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    protected $fillable = ['name', 'slug', 'active'];

    /** @use HasFactory<DistrictFactory> */
    use HasFactory;

    public function jobOffers(): HasMany
    {
        return $this->hasMany(JobOffer::class);
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function candidateProfiles(): HasMany
    {
        return $this->hasMany(CandidateProfile::class);
    }

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
