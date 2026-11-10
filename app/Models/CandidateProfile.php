<?php

namespace App\Models;

use Database\Factories\CandidateProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CandidateProfile extends Model
{
    protected $fillable = ['user_id', 'district_id', 'phone', 'bio', 'experience', 'cv_path'];

    /** @use HasFactory<CandidateProfileFactory> */
    use HasFactory;

    public function hasValidCv(): bool
    {
        $disk = Storage::disk('local');
        if (! $this->cv_path || ! $disk->exists($this->cv_path) || $disk->size($this->cv_path) > 5120 * 1024) {
            return false;
        }

        $stream = $disk->readStream($this->cv_path);
        if (! is_resource($stream)) {
            return false;
        }

        try {
            return fread($stream, 5) === '%PDF-' && $disk->mimeType($this->cv_path) === 'application/pdf';
        } finally {
            fclose($stream);
        }
    }

    public static function deleteUnusedCv(?string $path): void
    {
        if (! $path || static::where('cv_path', $path)->exists() || Application::where('cv_path', $path)->exists()) {
            return;
        }

        $disk = Storage::disk('local');
        if ($disk->exists($path) && ! $disk->delete($path)) {
            throw new \RuntimeException('No se pudo eliminar el CV anterior.');
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }
}
