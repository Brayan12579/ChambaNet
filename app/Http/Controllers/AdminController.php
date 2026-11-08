<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobOfferRequest;
use App\Models\Application;
use App\Models\Category;
use App\Models\Company;
use App\Models\District;
use App\Models\JobOffer;
use App\Models\Report;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    private const SECTIONS = [
        'users' => [User::class, 'Usuarios'],
        'companies' => [Company::class, 'Empresas'],
        'jobs' => [JobOffer::class, 'Ofertas laborales'],
        'categories' => [Category::class, 'Categorías'],
        'districts' => [District::class, 'Distritos'],
        'resources' => [Resource::class, 'Recursos'],
        'reports' => [Report::class, 'Reportes'],
    ];

    public function dashboard(): View
    {
        $pendingQuery = Company::where('verified', false)->whereHas('user', fn ($query) => $query->where('active', true)->where('role', 'employer'));
        $stats = [
            'Usuarios' => User::count(), 'Empresas' => Company::count(),
            'Ofertas publicadas' => JobOffer::published()->count(), 'Postulaciones' => Application::count(),
            'Reportes pendientes' => Report::where('status', 'pending')->count(),
            'Empresas por verificar' => (clone $pendingQuery)->count(),
        ];
        $reports = Report::with(['user', 'jobOffer'])->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")->latest()->take(5)->get();

        return view('admin.dashboard', [
            'stats' => $stats, 'reports' => $reports, 'sections' => self::SECTIONS,
            'pendingCompanies' => $pendingQuery->with('district')->latest()->take(5)->get(),
        ]);
    }

    public function index(Request $request, string $section): View
    {
        [$class, $title] = $this->definition($section);
        $filterOptions = $this->filterOptions($section);
        $rules = ['q' => ['nullable', 'string', 'max:160'], 'sort' => ['nullable', Rule::in(['recent', 'oldest'])]];
        foreach ($filterOptions as $key => $configuration) {
            $rules[$key] = ['nullable', Rule::in(array_map('strval', array_keys($configuration['options'])))];
        }
        $filters = $request->validate($rules);
        $search = trim($filters['q'] ?? '');
        $query = $class::query();
        if ($search !== '') {
            $query->where(function ($builder) use ($section, $search): void {
                $term = '%'.$search.'%';
                $builder->where(match ($section) { 'jobs', 'resources' => 'title', 'reports' => 'reason', default => 'name' }, 'like', $term);
                match ($section) {
                    'users' => $builder->orWhere('email', 'like', $term),
                    'companies' => $builder->orWhere('sector', 'like', $term),
                    'jobs' => $builder->orWhereHas('company', fn ($company) => $company->where('name', 'like', $term)),
                    'reports' => $builder->orWhere('details', 'like', $term)->orWhereHas('jobOffer', fn ($job) => $job->where('title', 'like', $term)),
                    'resources' => $builder->orWhere('excerpt', 'like', $term),
                    default => null,
                };
            });
        }
        foreach ($filterOptions as $key => $configuration) {
            if (! isset($filters[$key]) || $filters[$key] === '') {
                continue;
            }
            if ($section === 'jobs' && $key === 'status') {
                if ($filters[$key] === 'expired') {
                    $query->where('status', 'published')->whereDate('expires_at', '<', today());
                } elseif ($filters[$key] === 'published') {
                    $query->published();
                } else {
                    $query->where('status', $filters[$key]);
                }
            } else {
                $query->where($key, $filters[$key]);
            }
        }
        if ($section === 'reports') {
            $query->with(['jobOffer', 'user']);
        }
        if ($section === 'jobs') {
            $query->with('company');
        }
        $query->orderBy('id', ($filters['sort'] ?? 'recent') === 'oldest' ? 'asc' : 'desc');

        return view('admin.index', [
            'records' => $query->paginate(15)->withQueryString(), 'section' => $section, 'title' => $title,
            'sections' => self::SECTIONS, 'search' => $search, 'filters' => $filters, 'filterOptions' => $filterOptions,
        ]);
    }

    private function filterOptions(string $section): array
    {
        return match ($section) {
            'users' => [
                'role' => ['label' => 'Rol', 'options' => User::ROLES],
                'active' => ['label' => 'Cuenta', 'options' => ['1' => 'Activa', '0' => 'Inactiva']],
            ],
            'companies' => ['verified' => ['label' => 'Verificación', 'options' => ['1' => 'Verificadas', '0' => 'Sin verificar']]],
            'jobs' => ['status' => ['label' => 'Estado', 'options' => ['published' => 'Publicadas vigentes', 'expired' => 'Vencidas', 'draft' => 'Borradores', 'closed' => 'Cerradas']]],
            'reports' => ['status' => ['label' => 'Revisión', 'options' => Report::STATUSES]],
            'resources' => ['published' => ['label' => 'Publicación', 'options' => ['1' => 'Publicados', '0' => 'Borradores']]],
            default => ['active' => ['label' => 'Estado', 'options' => ['1' => 'Activos', '0' => 'Inactivos']]],
        };
    }

    public function verifyCompany(Company $company): RedirectResponse
    {
        $missing = collect(['name', 'description', 'sector', 'address', 'district_id'])->filter(fn (string $field): bool => blank($company->getAttribute($field)));
        if ($missing->isNotEmpty() || ! $company->user->active || $company->user->role !== 'employer') {
            throw ValidationException::withMessages(['company' => 'La empresa necesita un perfil completo y un empleador activo antes de verificarse. Revisa su información desde Editar.']);
        }
        $company->update(['verified' => true]);

        return back()->with('status', 'Empresa verificada tras la revisión administrativa.');
    }

    public function closeJob(JobOffer $job): RedirectResponse
    {
        $job->update(['status' => 'closed']);

        return back()->with('status', 'Oferta cerrada. Las postulaciones y los reportes se conservan.');
    }

    public function create(string $section): View
    {
        [$class] = $this->definition($section);
        abort_if($section === 'reports', 404);

        return $this->form($section, new $class);
    }

    public function edit(string $section, int $record): View
    {
        [$class] = $this->definition($section);

        return $this->form($section, $class::findOrFail($record));
    }

    private function form(string $section, Model $record): View
    {
        [, $title] = $this->definition($section);

        return view('admin.form', [
            'section' => $section, 'record' => $record, 'title' => $title, 'sections' => self::SECTIONS,
            'fields' => $this->fields($section),
            'categories' => Category::where('active', true)->orderBy('name')->get(),
            'districts' => District::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, string $section): RedirectResponse
    {
        [$class] = $this->definition($section);
        abort_if($section === 'reports', 404);
        $this->persist($request, $section, new $class);

        return redirect()->route('admin.content.index', $section)->with('status', 'Registro creado.');
    }

    public function update(Request $request, string $section, int $record): RedirectResponse
    {
        [$class] = $this->definition($section);
        $this->persist($request, $section, $class::findOrFail($record));

        return redirect()->route('admin.content.index', $section)->with('status', 'Registro actualizado.');
    }

    private function persist(Request $request, string $section, Model $record): void
    {
        $data = $request->validate($this->rules($section, $record, $request));
        foreach (match ($section) {
            'users', 'categories', 'districts' => ['active'],
            'companies' => ['verified'],
            'jobs' => ['featured'],
            'resources' => ['published'],
            default => [],
        } as $boolean) {
            $data[$boolean] = $request->boolean($boolean);
        }
        if ($section === 'users' && empty($data['password'])) {
            unset($data['password']);
        }
        if (in_array($section, ['categories', 'districts', 'companies', 'jobs', 'resources'], true) && ! $record->exists) {
            $data['slug'] = Str::slug($data['name'] ?? $data['title']).'-'.Str::lower(Str::random(8));
        }
        DB::transaction(function () use ($request, $section, $record, $data): void {
            if ($section === 'users') {
                User::where('role', 'admin')->where('active', true)->lockForUpdate()->get();
                if ($record->id === $request->user()->id && ($data['role'] !== 'admin' || ! $data['active'])) {
                    throw ValidationException::withMessages(['role' => 'No puedes quitarte el acceso administrativo.']);
                }
                if ($record->exists && $record->role === 'admin' && $record->active && ($data['role'] !== 'admin' || ! $data['active']) && User::where('role', 'admin')->where('active', true)->count() <= 1) {
                    throw ValidationException::withMessages(['role' => 'Debe existir al menos un administrador activo.']);
                }
                if ($record->exists && $record->role === 'employer' && $data['role'] !== 'employer' && $record->company?->jobOffers()->exists()) {
                    throw ValidationException::withMessages(['role' => 'Este empleador tiene ofertas. Desactiva la cuenta si necesitas bloquearla y conserva su rol.']);
                }
            }
            $record->fill($data)->save();
            if ($section === 'users' && $record->role === 'candidate') {
                $record->candidateProfile()->firstOrCreate([]);
            }
            if ($section === 'users' && $record->role === 'employer') {
                $record->company()->firstOrCreate([], ['name' => $record->name, 'slug' => Str::slug($record->name).'-'.Str::lower(Str::random(8))]);
            }
        });
    }

    public function destroy(Request $request, string $section, int $record): RedirectResponse
    {
        [$class] = $this->definition($section);
        $model = $class::findOrFail($record);
        if ($section === 'users' && ($model->id === $request->user()->id || $model->role === 'admin')) {
            return back()->withErrors(['delete' => 'No puedes eliminar tu cuenta ni una cuenta administrativa.']);
        }
        if (($section === 'jobs' && ($model->applications()->exists() || $model->reports()->exists())) ||
            ($section === 'companies' && $model->jobOffers()->exists()) ||
            ($section === 'users' && ($model->company()->exists() || $model->applications()->exists() || $model->reports()->exists()))) {
            return back()->withErrors(['delete' => 'El registro tiene historial relacionado. Desactívalo o cierra sus ofertas para conservarlo.']);
        }
        try {
            $model->delete();
        } catch (QueryException $exception) {
            if (! in_array((string) $exception->getCode(), ['23000', '23503'], true)) {
                throw $exception;
            }

            return back()->withErrors(['delete' => 'El registro está en uso. Puedes desactivarlo desde Editar.']);
        }

        return back()->with('status', 'Registro eliminado.');
    }

    public function applications(): View
    {
        return view('admin.applications', ['applications' => Application::with(['user', 'jobOffer.company'])->latest()->paginate(20), 'sections' => self::SECTIONS]);
    }

    private function definition(string $section): array
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);

        return self::SECTIONS[$section];
    }

    private function rules(string $section, Model $record, Request $request): array
    {
        return match ($section) {
            'users' => [
                'name' => ['required', 'string', 'max:160'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($record->id)],
                'password' => [$record->exists ? 'nullable' : 'required', 'confirmed', Password::min(8)],
                'role' => ['required', Rule::in(['candidate', 'employer', 'admin'])],
                'active' => ['boolean'],
            ],
            'categories', 'districts' => ['name' => ['required', 'string', 'max:120', Rule::unique($section)->ignore($record->id)], 'active' => ['boolean']],
            'companies' => [
                'name' => ['required', 'string', 'max:160'],
                'user_id' => ['required', Rule::exists('users', 'id')->where('role', 'employer'), Rule::unique('companies', 'user_id')->ignore($record->id)],
                'district_id' => ['required', 'exists:districts,id'],
                'sector' => ['required', 'string', 'max:120'],
                'description' => ['required', 'string', 'max:5000'],
                'address' => ['required', 'string', 'max:255'],
                'website' => ['nullable', 'url:http,https', 'max:255'],
                'verified' => ['boolean'],
            ],
            'jobs' => [...JobOfferRequest::createFrom($request)->rules(), 'company_id' => ['required', 'exists:companies,id'], 'featured' => ['boolean']],
            'resources' => ['title' => ['required', 'string', 'max:180'], 'excerpt' => ['required', 'string', 'max:500'], 'body' => ['required', 'string', 'max:30000'], 'published' => ['boolean']],
            'reports' => ['status' => ['required', Rule::in(['pending', 'reviewed', 'dismissed'])]],
        };
    }

    private function fields(string $section): array
    {
        return match ($section) {
            'users' => [
                'name' => ['Nombre completo', 'text'], 'email' => ['Correo', 'email'],
                'password' => ['Contraseña (dejar vacía para conservar)', 'password'],
                'password_confirmation' => ['Confirmar contraseña', 'password'],
                'role' => ['Rol', 'select', ['candidate' => 'Candidato', 'employer' => 'Empleador', 'admin' => 'Administrador']],
                'active' => ['Cuenta activa', 'checkbox'],
            ],
            'categories', 'districts' => ['name' => ['Nombre', 'text'], 'active' => ['Activo', 'checkbox']],
            'companies' => [
                'name' => ['Nombre comercial', 'text'],
                'user_id' => ['Empleador propietario', 'select', User::where('role', 'employer')->orderBy('name')->pluck('email', 'id')->all()],
                'district_id' => ['Distrito', 'select', District::orderBy('name')->pluck('name', 'id')->all()],
                'sector' => ['Rubro', 'text'], 'description' => ['Descripción', 'textarea'],
                'address' => ['Dirección', 'text'], 'website' => ['Sitio web', 'url'], 'verified' => ['Empresa verificada', 'checkbox'],
            ],
            'jobs' => ['company_id' => ['Empresa', 'select', Company::orderBy('name')->pluck('name', 'id')->all()], 'featured' => ['Destacada', 'checkbox']],
            'resources' => ['title' => ['Título', 'text'], 'excerpt' => ['Resumen', 'textarea'], 'body' => ['Contenido (texto, sin HTML)', 'textarea'], 'published' => ['Publicado', 'checkbox']],
            'reports' => ['status' => ['Estado', 'select', ['pending' => 'Pendiente', 'reviewed' => 'Revisado', 'dismissed' => 'Descartado']]],
        };
    }
}
