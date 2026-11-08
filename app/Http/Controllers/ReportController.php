<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Models\JobOffer;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    public function store(ReportRequest $request, JobOffer $job): RedirectResponse
    {
        abort_unless($job->isOpen(), 404);
        $job->reports()->updateOrCreate(['user_id' => $request->user()->id], [
            'reason' => $request->validated('reason'),
            'details' => $request->validated('details') ?? '',
            'status' => 'pending',
        ]);

        return back()->with('success', 'Recibimos tu reporte. El equipo de administración revisará esta publicación.');
    }
}
