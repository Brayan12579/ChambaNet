<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'active'];

    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    public function jobOffers(): HasMany
    {
        return $this->hasMany(JobOffer::class);
    }

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
