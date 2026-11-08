<?php

namespace App\Models;

use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = ['job_offer_id', 'user_id', 'cv_path', 'cover_letter', 'status'];

    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    public const STATUSES = ['pending' => 'Pendiente', 'reviewing' => 'En revisión', 'accepted' => 'Aceptada', 'rejected' => 'Rechazada', 'withdrawn' => 'Retirada'];

    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }

    public function job(): BelongsTo
    {
        return $this->jobOffer();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
