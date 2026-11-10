@extends('layouts.app')
@section('title', 'Postulaciones recibidas')
@section('content')
<section class="section container">
    <div class="page-heading workspace-heading"><div><p class="eyebrow">BANDEJA DE CANDIDATOS</p><h1>{{ $job?->title ?? 'Postulaciones recibidas' }}</h1><p>{{ $applications->total() }} resultados · Revisa los perfiles y comunica el avance del proceso.</p></div><a class="button-secondary" href="{{ route('employer.jobs.index') }}">Ver mis ofertas</a></div>
    <div class="dashboard-layout">@include('employer.sidebar')<div class="dashboard-main stack">
        <div class="status-shortcuts" aria-label="Resumen de postulaciones"><span>{{ $applicationCounts->sum() }} en total</span>@foreach(['pending', 'reviewing', 'accepted'] as $state)<a href="{{ $job ? route('employer.jobs.applicants', [$job, 'status' => $state]) : route('employer.applications.index', ['status' => $state]) }}">{{ $applicationCounts[$state] ?? 0 }} {{ \App\Models\Application::STATUSES[$state] }}</a>@endforeach</div>
        <form class="panel stack" method="GET" action="{{ $job ? route('employer.jobs.applicants', $job) : route('employer.applications.index') }}" role="search">
            <div class="form-grid">
                <div class="field"><label for="applicant-search">Buscar candidato</label><input id="applicant-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nombre o correo" maxlength="120"></div>
                <div class="field"><label for="applicant-status">Estado de postulación</label><select id="applicant-status" name="status"><option value="">Todos los estados</option>@foreach(\App\Models\Application::STATUSES as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
                @unless($job)<div class="field"><label for="applicant-job">Oferta laboral</label><select id="applicant-job" name="job"><option value="">Todas mis ofertas</option>@foreach($offers as $offer)<option value="{{ $offer->id }}" @selected((string) ($filters['job'] ?? '') === (string) $offer->id)>{{ $offer->title }}</option>@endforeach</select></div>@endunless
                <div class="field"><label for="applicant-sort">Orden</label><select id="applicant-sort" name="sort"><option value="recent" @selected(($filters['sort'] ?? 'recent') === 'recent')>Más recientes</option><option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Más antiguos</option></select></div>
            </div>
            <div class="actions"><button class="button" type="submit"><x-icon name="search" :size="18" />Filtrar postulaciones</button>@if(collect($filters)->except('sort')->filter(fn ($value) => filled($value))->isNotEmpty())<a class="text-link" href="{{ $job ? route('employer.jobs.applicants', $job) : route('employer.applications.index') }}">Limpiar filtros</a>@endif</div>
        </form>
        @forelse($applications as $application)<article class="panel">
            <div class="page-heading"><div><x-application-status :status="$application->status" /><h2>{{ $application->user->name }}</h2><p class="muted">Postuló el {{ $application->created_at->format('d/m/Y H:i') }}</p></div>@if($application->cv_path)<a class="button-secondary" href="{{ route('applications.cv', $application) }}" target="_blank" rel="noopener">Revisar CV en PDF ↗</a>@endif</div>
            @unless($job)<p class="application-offer"><x-icon name="briefcase" :size="18" /><a class="text-link" href="{{ route('employer.jobs.applicants', $application->jobOffer) }}">{{ $application->jobOffer->title }}</a></p>@endunless
            @if($application->user->candidateProfile?->bio)<p>{{ $application->user->candidateProfile->bio }}</p>@endif
            @if($application->cover_letter)<h3>Mensaje del candidato</h3><p style="white-space:pre-line">{{ $application->cover_letter }}</p>@endif
            @if($application->status !== 'withdrawn')
                <p class="muted">{{ $application->user->email }} @if($application->user->candidateProfile?->phone) · {{ $application->user->candidateProfile->phone }} @endif</p>
                <form action="{{ route('employer.applications.update', $application) }}" method="POST" class="actions">@csrf @method('PATCH')<label for="application-{{ $application->id }}">Actualizar estado</label><select id="application-{{ $application->id }}" name="status">@foreach(\App\Models\Application::STATUSES as $value => $label)@if($value !== 'withdrawn')<option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>@endif @endforeach</select><button class="button" type="submit">Guardar estado</button></form>
            @else<p class="muted">El candidato retiró esta postulación. Se conserva el historial.</p>@endif
        </article>@empty<div class="empty-state panel"><x-icon name="users" :size="34" /><h2>No hay postulaciones para mostrar</h2><p>Si aplicaste filtros, prueba con otro estado o candidato. Las nuevas postulaciones a tus ofertas aparecerán aquí.</p></div>@endforelse
        {{ $applications->links() }}
    </div></div>
</section>
@endsection
