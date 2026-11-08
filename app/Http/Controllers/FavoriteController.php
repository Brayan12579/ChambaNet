<?php

namespace App\Http\Controllers;

use App\Models\JobOffer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        return view('candidate.favorites', [
            'jobs' => $request->user()->favorites()->with(['company', 'district', 'category'])->orderByPivot('created_at', 'desc')->paginate(12),
        ]);
    }

    public function toggle(Request $request, JobOffer $job): RedirectResponse
    {
        abort_unless($request->user()->role === 'candidate', 403);
        $saved = DB::transaction(function () use ($request, $job): bool {
            $user = $request->user();
            $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($user->favorites()->where('job_offers.id', $job->id)->exists()) {
                $user->favorites()->detach($job->id);

                return false;
            }

            abort_unless($job->isOpen(), 404);
            $user->favorites()->attach($job->id);

            return true;
        });

        return back()->with('success', $saved ? 'Oferta guardada en tus favoritos.' : 'Oferta eliminada de tus favoritos.');
    }
}
