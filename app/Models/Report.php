<?php

namespace App\Models;

use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = ['user_id', 'job_offer_id', 'reason', 'details', 'status'];

    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    public const STATUSES = ['pending' => 'Pendiente', 'reviewed' => 'Revisado', 'dismissed' => 'Descartado'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }
}
