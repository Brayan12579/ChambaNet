<?php

namespace App\Http\Controllers;

use App\Http\Requests\CandidateProfileRequest;
use App\Models\District;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CandidateController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        return view('candidate.dashboard', [
            'profile' => $user->candidateProfile,
            'applications' => $user->applications()->with(['jobOffer.company', 'jobOffer.district'])->latest()->limit(5)->get(),
            'savedCount' => $user->favorites()->count(),
            'applicationCounts' => $user->applications()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
            ...$this->profileSummary($user),
        ]);
    }

    public function edit(Request $request): View
    {
        return view('candidate.profile', [
            'profile' => $request->user()->candidateProfile,
            'districts' => District::query()->orderBy('name')->get(),
            ...$this->profileSummary($request->user()),
        ]);
    }

    /** @return array{completion: array<string, mixed>, cvInfo: array<string, mixed>} */
    private function profileSummary(User $user): array
    {
        $profile = $user->candidateProfile;
        $hasCv = filled($profile?->cv_path) && Storage::disk('local')->exists($profile->cv_path);
        $fields = [
            'name' => ['label' => 'Nombre completo', 'complete' => filled(trim($user->name)), 'tip' => 'Usa el nombre con el que te presentarás a las empresas.'],
            'email' => ['label' => 'Correo de contacto', 'complete' => filled(trim($user->email)), 'tip' => 'Mantén un correo al que puedas acceder.'],
            'phone' => ['label' => 'Teléfono de contacto', 'complete' => filled(trim($profile?->phone ?? '')), 'tip' => 'Agrega un teléfono para que la empresa pueda contactarte.'],
            'district_id' => ['label' => 'Tu distrito', 'complete' => filled($profile?->district_id), 'tip' => 'Indica el distrito desde el que buscas oportunidades.'],
            'bio' => ['label' => 'Presentación personal', 'complete' => filled(trim($profile?->bio ?? '')), 'tip' => 'Describe tus habilidades y qué tipo de puesto buscas.'],
            'experience' => ['label' => 'Experiencia o actividades', 'complete' => filled(trim($profile?->experience ?? '')), 'tip' => 'Cuenta un empleo, práctica, proyecto o actividad. También puedes indicar que buscas tu primera experiencia.'],
            'cv' => ['label' => 'CV en PDF', 'complete' => $hasCv, 'tip' => 'Sube un PDF actualizado. Solo se comparte con las empresas a las que postulas.'],
        ];
        $completed = collect($fields)->where('complete', true)->count();

        return [
            'completion' => ['percentage' => (int) round($completed / count($fields) * 100), 'completed' => $completed, 'total' => count($fields), 'missing' => array_filter($fields, fn (array $field): bool => ! $field['complete'])],
            'cvInfo' => ['exists' => $hasCv, 'sizeKb' => $hasCv ? (int) ceil(Storage::disk('local')->size($profile->cv_path) / 1024) : 0, 'updatedAt' => $hasCv ? \Illuminate\Support\Carbon::createFromTimestamp(Storage::disk('local')->lastModified($profile->cv_path)) : null],
        ];
    }

    public function update(CandidateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $oldPath = $user->candidateProfile?->cv_path;
        $newPath = null;

        try {
            if ($request->hasFile('cv')) {
                $newPath = $request->file('cv')->store('cvs/profiles', 'local');
                if (! $newPath) {
                    throw new RuntimeException('No se pudo almacenar el documento PDF.');
                }
            }

            DB::transaction(function () use ($user, $data, $newPath): void {
                $user->fill(['name' => $data['name'], 'email' => $data['email']]);
                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }
                $user->save();

                $profile = collect($data)->only(['phone', 'district_id', 'bio', 'experience'])->all();
                if ($newPath) {
                    $profile['cv_path'] = $newPath;
                }
                $user->candidateProfile()->updateOrCreate(['user_id' => $user->id], $profile);
            });
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }
            throw $exception;
        }

        if ($newPath && $oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return back()->with('success', 'Tu perfil se actualizó correctamente.');
    }

    public function cv(Request $request): StreamedResponse
    {
        $path = $request->user()->candidateProfile?->cv_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, 'mi-cv.pdf', [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "frame-ancestors 'self'",
        ]);
    }
}
