<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Models\District;
use App\Models\JobOffer;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicController extends Controller
{
    public function home(Request $request): View
    {
        return view('public.home', [
            'jobs' => JobOffer::published()->withSavedState($request->user())->with(['company', 'category', 'district'])->latest()->take(6)->get(),
            'featured' => JobOffer::published()->withSavedState($request->user())->with(['company', 'category', 'district'])->where('featured', true)->latest()->take(3)->get(),
            'categories' => Category::where('active', true)->withCount(['jobOffers' => fn ($query) => $query->published()])->take(6)->get(),
            'districts' => District::where('active', true)->orderBy('name')->get(),
            'companies' => Company::whereHas('user', fn ($query) => $query->where('active', true)->where('role', 'employer'))->withCount(['jobOffers' => fn ($query) => $query->published()])->orderByDesc('verified')->take(4)->get(),
            'resources' => Resource::where('published', true)->latest()->take(3)->get(),
            'jobCount' => JobOffer::published()->count(),
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'integer', 'exists:districts,id'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'schedule' => ['nullable', 'in:full_time,part_time,shifts'],
            'contract_type' => ['nullable', 'in:permanent,temporary,internship'],
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'verified' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:recent,salary'],
        ]);

        return view('public.jobs', [
            'jobs' => JobOffer::published()->withSavedState($request->user())->with(['company', 'category', 'district'])->filter($filters)->paginate(12)->withQueryString(),
            'filters' => $filters,
            'categories' => Category::where('active', true)->orderBy('name')->get(),
            'districts' => District::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, JobOffer $job): View
    {
        $job->load(['company.user', 'category', 'district']);
        $user = $request->user();
        $canManage = $user?->active && ($user->role === 'admin' || ($user->role === 'employer' && $job->company->user_id === $user->id));
        $application = $user?->applications()->where('job_offer_id', $job->id)->first();
        $isSaved = $user?->favorites()->where('job_offer_id', $job->id)->exists() ?? false;
        $canViewHistory = $user?->active && $user->role === 'candidate'
            && in_array($job->status, ['published', 'closed'], true)
            && ($application !== null || $isSaved);
        abort_unless($job->isOpen() || $canManage || $canViewHistory, 404);

        return view('public.show', [
            'job' => $job,
            'related' => JobOffer::published()->withSavedState($request->user())->with(['company', 'category', 'district'])->where('category_id', $job->category_id)->whereKeyNot($job->id)->latest()->take(3)->get(),
            'application' => $application,
            'isSaved' => $isSaved,
        ]);
    }

    public function companies(): View
    {
        return view('public.companies', ['companies' => Company::with('district')->whereHas('user', fn ($query) => $query->where('active', true)->where('role', 'employer'))->withCount(['jobOffers' => fn ($query) => $query->published()])->orderByDesc('verified')->orderBy('name')->paginate(12)]);
    }

    public function company(Request $request, Company $company): View
    {
        abort_unless($company->user->active && $company->user->role === 'employer', 404);

        return view('public.company', ['company' => $company->load('district'), 'jobs' => $company->jobOffers()->published()->withSavedState($request->user())->with(['company', 'category', 'district'])->latest()->paginate(9)]);
    }

    public function resources(): View
    {
        return view('public.resources', ['resources' => Resource::where('published', true)->latest()->paginate(9)]);
    }

    public function resource(Resource $resource): View
    {
        abort_unless($resource->published, 404);

        return view('public.resource', compact('resource'));
    }

    public function logo(Company $company): BinaryFileResponse
    {
        abort_unless($company->user->active && $company->logo_path && Storage::disk('local')->exists($company->logo_path), 404);

        return response()->file(Storage::disk('local')->path($company->logo_path), ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'public, max-age=3600']);
    }
}
