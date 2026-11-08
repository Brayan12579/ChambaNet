<?php

namespace App\Models;

use Database\Factories\SubscriptionPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = ['name', 'slug', 'monthly_price', 'vacancy_limit', 'features', 'active'];

    /** @use HasFactory<SubscriptionPlanFactory> */
    use HasFactory;

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    protected function casts(): array
    {
        return ['monthly_price' => 'decimal:2', 'features' => 'array', 'active' => 'boolean'];
    }
}
