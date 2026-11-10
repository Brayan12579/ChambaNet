<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyRequest;
use App\Models\Application;
use App\Models\JobOffer;
use App\Models\User;
use App\Notifications\ApplicationReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(Application::STATUSES))],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        return view('candidate.applications', [
            'filters' => $filters,
            'applications' => $request->user()->applications()->with(['jobOffer.company', 'jobOffer.district'])
                ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
                ->when(filled($filters['q'] ?? null), fn ($query) => $query->whereHas('jobOffer', fn ($jobs) => $jobs
                    ->where(fn ($jobs) => $jobs->where('title', 'like', '%'.$filters['q'].'%')
                        ->orWhereHas('company', fn ($companies) => $companies->where('name', 'like', '%'.$filters['q'].'%')))))
                ->latest()->orderByDesc('id')->paginate(12)->withQueryString(),
        ]);
    }

    public function store(ApplyRequest $request, JobOffer $job): RedirectResponse
    {
        $user = $request->user();
        $snapshotPath = null;

        try {
            DB::transaction(function () use ($request, $job, $user, &$snapshotPath): void {
                $offer = JobOffer::query()->published()->lockForUpdate()->findOrFail($job->id);
                User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                if ($user->applications()->where('job_offer_id', $offer->id)->exists()) {
                    throw ValidationException::withMessages(['application' => 'Ya enviaste una postulación a esta oferta. Puedes consultar su estado en tu panel.']);
                }

                if ($request->hasFile('cv')) {
                    $snapshotPath = $request->file('cv')->store('cvs/applications', 'local');
                    if (! $snapshotPath) {
                        throw new RuntimeException('No se pudo almacenar el documento PDF.');
                    }
                } else {
                    $profile = $user->candidateProfile()->first();
                    if ($profile?->hasValidCv()) {
                        $snapshotPath = 'cvs/applications/'.Str::uuid().'.pdf';
                        if (! Storage::disk('local')->copy($profile->cv_path, $snapshotPath)) {
                            throw new RuntimeException('No se pudo adjuntar el documento PDF.');
                        }
                    } elseif ($offer->cv_required) {
                        throw ValidationException::withMessages(['cv' => 'Adjunta un CV en PDF o carga uno primero en tu perfil.']);
                    }
                }

                $application = $user->applications()->create([
                    'job_offer_id' => $offer->id,
                    'cv_path' => $snapshotPath,
                    'cover_letter' => $request->validated('cover_letter'),
                    'status' => 'pending',
                ]);
                $application->setRelation('jobOffer', $offer);
                $offer->company->user->notify(new ApplicationReceived($application));
            });
        } catch (Throwable $exception) {
            if ($snapshotPath) {
                Storage::disk('local')->delete($snapshotPath);
            }
            throw $exception;
        }

        return redirect()->route('candidate.applications.index')->with('success', 'Postulación enviada. Podrás seguir su estado desde este panel.');
    }

    public function withdraw(Request $request, Application $application): RedirectResponse
    {
        abort_unless($application->user_id === $request->user()->id, 403);
        $updated = Application::query()->whereKey($application->id)
            ->whereIn('status', ['pending', 'reviewing'])
            ->update(['status' => 'withdrawn', 'updated_at' => now()]);

        if (! $updated) {
            return back()->withErrors(['application' => 'Solo puedes retirar postulaciones pendientes o en revisión.']);
        }

        return back()->with('success', 'Retiraste tu postulación. No podrás enviarla otra vez a esta misma oferta.');
    }

    public function cv(Request $request, Application $application): StreamedResponse
    {
        $user = $request->user();
        $isOwner = $application->user_id === $user->id;
        $isRecipient = $user->role === 'employer' && $application->jobOffer->company->user_id === $user->id;
        abort_unless($isOwner || $isRecipient, 403);
        abort_unless($application->cv_path && Storage::disk('local')->exists($application->cv_path), 404);

        return Storage::disk('local')->response($application->cv_path, 'cv-postulacion-'.$application->id.'.pdf', [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "frame-ancestors 'self'",
        ]);
    }
}
