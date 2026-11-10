<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobOfferRequest;
use App\Models\Application;
use App\Models\Category;
use App\Models\Company;
use App\Models\District;
use App\Models\JobOffer;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmployerController extends Controller
{
    private function companyFor(Request $request): Company
    {
        return $request->user()->company()->firstOrCreate([], [
            'name' => $request->user()->name,
            'slug' => Str::slug($request->user()->name).'-'.Str::lower(Str::random(8)),
        ]);
    }

    public function dashboard(Request $request): View
    {
        $company = $this->companyFor($request);
        $jobs = $company->jobOffers()->withCount(['applications', 'applications as pending_count' => fn ($query) => $query->where('status', 'pending')])->latest()->take(5)->get();
        $received = Application::whereHas('jobOffer', fn ($query) => $query->where('company_id', $company->id));
        $stats = [
            'Ofertas activas' => $company->jobOffers()->published()->count(),
            'Ofertas vencidas' => $company->jobOffers()->where('status', 'published')->whereDate('expires_at', '<', today())->count(),
            'Ofertas cerradas' => $company->jobOffers()->where('status', 'closed')->count(),
            'Borradores' => $company->jobOffers()->where('status', 'draft')->count(),
            'Postulaciones recibidas' => (clone $received)->count(),
            'Por revisar' => (clone $received)->where('status', 'pending')->count(),
        ];
        $companyMissingFields = $this->missingCompanyFields($company);

        $recentApplications = (clone $received)->with(['user', 'jobOffer'])->latest()->take(4)->get();

        return view('employer.dashboard', compact('company', 'jobs', 'stats', 'companyMissingFields', 'recentApplications'));
    }

    public function company(Request $request): View
    {
        return view('employer.company', ['company' => $this->companyFor($request), 'districts' => District::where('active', true)->orderBy('name')->get()]);
    }

    public function updateCompany(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'sector' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:255'],
            'district_id' => ['required', Rule::exists('districts', 'id')->where('active', true)],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=3000,max_height=3000'],
        ]);
        $company = $this->companyFor($request);
        unset($data['logo']);
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('company-logos', 'local');
        }
        $oldLogo = $company->logo_path;
        $company->fill($data);
        if ($company->isDirty(['name', 'address', 'district_id', 'website', 'logo_path'])) {
            $company->verified = false;
        }
        $company->save();
        if (isset($data['logo_path']) && $oldLogo) {
            Storage::disk('local')->delete($oldLogo);
        }

        return back()->with('status', 'Información de la empresa actualizada.');
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['draft', 'published', 'expired', 'closed'])],
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        $status = (string) ($filters['status'] ?? '');
        $jobs = $this->companyFor($request)->jobOffers()->with(['category', 'district'])->withCount('applications')
            ->when($status === 'published', fn ($query) => $query->published())
            ->when($status === 'expired', fn ($query) => $query->where('status', 'published')->whereDate('expires_at', '<', today()))
            ->when(in_array($status, ['draft', 'closed'], true), fn ($query) => $query->where('status', $status))
            ->when(filled($filters['q'] ?? null), fn ($query) => $query->where('title', 'like', '%'.$filters['q'].'%'))
            ->latest()->orderByDesc('id')->paginate(10)->withQueryString();

        return view('employer.jobs', compact('jobs', 'status', 'filters'));
    }

    public function create(Request $request): View
    {
        return $this->form(new JobOffer(['status' => 'draft', 'vacancies' => 1, 'schedule' => 'full_time', 'contract_type' => 'temporary']), $this->companyFor($request));
    }

    public function store(JobOfferRequest $request): RedirectResponse
    {
        $company = $this->companyFor($request);
        if ($this->missingCompanyFields($company) !== []) {
            return redirect()->route('employer.company.edit')->with('status', 'Completa el perfil de tu empresa antes de publicar o guardar una oferta.');
        }
        $data = $request->validated();
        unset($data['intent']);
        $job = $company->jobOffers()->create([...$data, 'slug' => Str::slug($request->string('title')).'-'.Str::lower(Str::random(8))]);

        if ($request->input('intent') === 'preview') {
            return redirect()->route('jobs.show', $job)->with('status', 'Borrador guardado. Solo tu empresa puede ver esta vista previa; todavía no está publicado.');
        }

        return redirect()->route('employer.jobs.edit', $job)->with('status', $job->status === 'published' ? 'Oferta publicada. Ya puede recibir postulaciones.' : 'Oferta guardada. Puedes previsualizarla y publicarla cuando esté lista.');
    }

    public function edit(JobOffer $job): View
    {
        Gate::authorize('update', $job);

        return $this->form($job, $job->company);
    }

    private function form(JobOffer $job, Company $company): View
    {
        return view('employer.job-form', [
            'job' => $job,
            'company' => $company,
            'companyMissingFields' => $this->missingCompanyFields($company),
            'categories' => Category::where('active', true)->orderBy('name')->get(),
            'districts' => District::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(JobOfferRequest $request, JobOffer $job): RedirectResponse
    {
        Gate::authorize('update', $job);
        if ($request->input('intent') === 'preview' && $job->status !== 'draft') {
            return redirect()->route('jobs.show', $job)->with('status', 'Vista de la versión guardada. No se modificó la publicación.');
        }
        $data = $request->validated();
        unset($data['intent']);
        if ($data['status'] === 'published' && $this->missingCompanyFields($job->company) !== []) {
            return redirect()->route('employer.company.edit')->with('status', 'Completa el perfil de tu empresa antes de publicar esta oferta.');
        }
        $job->update($data);
        if ($request->input('intent') === 'preview') {
            return redirect()->route('jobs.show', $job)->with('status', 'Borrador actualizado. Revisa la vista previa antes de publicar.');
        }

        return back()->with('status', 'Oferta actualizada.');
    }

    public function close(JobOffer $job): RedirectResponse
    {
        Gate::authorize('update', $job);
        $job->update(['status' => 'closed']);

        return back()->with('status', 'Oferta cerrada. Se conserva el historial de postulaciones.');
    }

    public function destroy(JobOffer $job): RedirectResponse
    {
        Gate::authorize('delete', $job);
        try {
            $deleted = DB::transaction(function () use ($job): bool {
                $lockedJob = JobOffer::whereKey($job->id)->lockForUpdate()->firstOrFail();
                if ($lockedJob->applications()->exists() || $lockedJob->reports()->exists()) {
                    return false;
                }

                return $lockedJob->delete();
            });
        } catch (QueryException $exception) {
            if (! in_array((string) $exception->getCode(), ['23000', '23503'], true)) {
                throw $exception;
            }
            $deleted = false;
        }
        if (! $deleted) {
            return back()->withErrors(['job' => 'Esta oferta tiene historial. Ciérrala para conservar las postulaciones y reportes.']);
        }

        return redirect()->route('employer.jobs.index')->with('status', 'Oferta eliminada.');
    }

    public function applicants(Request $request, ?JobOffer $job = null): View
    {
        if ($job) {
            Gate::authorize('update', $job);
        }
        $company = $this->companyFor($request);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys(Application::STATUSES))],
            'sort' => ['nullable', Rule::in(['recent', 'oldest'])],
            'job' => ['nullable', Rule::exists('job_offers', 'id')->where('company_id', $company->id)],
        ]);
        $query = Application::whereHas('jobOffer', fn ($query) => $query->where('company_id', $company->id))
            ->when($job, fn ($query) => $query->where('job_offer_id', $job->id));
        $applicationCounts = (clone $query)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $applications = $query->with(['user.candidateProfile', 'jobOffer'])
            ->when(! $job && filled($filters['job'] ?? null), fn ($query) => $query->where('job_offer_id', $filters['job']))
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $query->whereHas('user', fn ($users) => $users->where('name', 'like', '%'.$filters['q'].'%')->orWhere('email', 'like', '%'.$filters['q'].'%'));
            })
            ->orderBy('created_at', ($filters['sort'] ?? 'recent') === 'oldest' ? 'asc' : 'desc')
            ->orderBy('id', ($filters['sort'] ?? 'recent') === 'oldest' ? 'asc' : 'desc')
            ->paginate(15)->withQueryString();
        $offers = $company->jobOffers()->orderBy('title')->get(['id', 'title']);

        return view('employer.applicants', compact('job', 'applications', 'filters', 'applicationCounts', 'offers'));
    }

    public function updateApplication(Request $request, Application $application): RedirectResponse
    {
        Gate::authorize('update', $application->jobOffer);
        $data = $request->validate(['status' => ['required', Rule::in(['pending', 'reviewing', 'accepted', 'rejected'])]]);
        $changed = DB::transaction(function () use ($application, $data): bool {
            $lockedApplication = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            abort_if($lockedApplication->status === 'withdrawn', 422, 'Una postulación retirada no puede modificarse.');
            if ($lockedApplication->status === $data['status']) {
                return false;
            }
            $lockedApplication->update($data);
            $lockedApplication->user->notify(new ApplicationStatusChanged($lockedApplication));

            return true;
        });

        return back()->with('status', $changed ? 'Estado actualizado. El candidato recibió una notificación en su cuenta.' : 'La postulación ya tenía ese estado. No se enviaron notificaciones duplicadas.');
    }

    /** @return array<string, string> */
    private function missingCompanyFields(Company $company): array
    {
        return array_filter([
            'name' => 'nombre comercial',
            'description' => 'descripción',
            'district_id' => 'distrito',
            'sector' => 'rubro',
            'address' => 'dirección',
        ], fn (string $label, string $field) => blank($company->getAttribute($field)), ARRAY_FILTER_USE_BOTH);
    }
}
