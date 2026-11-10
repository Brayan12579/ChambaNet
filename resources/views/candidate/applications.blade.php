@extends('layouts.app')
@section('title', 'Mis postulaciones')
@section('content')
<section class="container section">
    <div class="page-heading"><div><span class="eyebrow">CADA PASO CUENTA</span><h1>Mis postulaciones</h1><p class="muted">Consulta tus solicitudes y las respuestas de cada empresa.</p></div><a class="button button-secondary" href="{{ route('jobs.index') }}">Buscar más empleos</a></div>
    <div class="dashboard-layout">
        @include('candidate._nav')
        <div class="dashboard-main stack">
            <form class="panel stack" method="GET" action="{{ route('candidate.applications.index') }}" role="search"><div class="form-grid"><div class="field"><label for="application-search">Puesto o empresa</label><input type="search" id="application-search" name="q" maxlength="120" value="{{ $filters['q'] ?? '' }}" placeholder="Encuentra una postulación"></div><div class="field"><label for="application-status">Estado de tu postulación</label><select id="application-status" name="status"><option value="">Todos los estados</option>@foreach(\App\Models\Application::STATUSES as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div></div><div class="actions"><button class="button-secondary" type="submit"><x-icon name="sliders" :size="18" />Filtrar postulaciones</button>@if(collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty())<a class="text-link" href="{{ route('candidate.applications.index') }}">Limpiar filtros</a>@endif<span class="muted">{{ $applications->total() }} resultados</span></div></form>
            @forelse($applications as $application)
                <article class="panel stack">
                    <div class="page-heading"><div><h2><a href="{{ route('jobs.show', $application->jobOffer) }}">{{ $application->jobOffer->title }}</a></h2><p class="muted">{{ $application->jobOffer->company->name }} · {{ $application->jobOffer->location_label }}</p></div><x-application-status :status="$application->status" /></div>
                    <x-application-status :status="$application->status" :timeline="true" :show-badge="false" />
                    <p class="muted">Postulaste el {{ $application->created_at->format('d/m/Y \a \l\a\s H:i') }}.</p>
                    @if($application->cover_letter)<p>{{ $application->cover_letter }}</p>@endif
                    <div class="actions">
                        @if($application->cv_path)<a class="button button-secondary" href="{{ route('applications.cv', $application) }}" target="_blank" rel="noopener">Ver CV enviado ↗</a>@else<span class="muted">Sin CV adjunto</span>@endif
                        @if(in_array($application->status, ['pending', 'reviewing']))
                            <form method="POST" action="{{ route('candidate.applications.withdraw', $application) }}" data-confirm="¿Retirar esta postulación? No podrás volver a postular a esta misma oferta.">@csrf @method('PATCH')<button class="button button-ghost" type="submit">Retirar postulación</button></form>
                        @endif
                    </div>
                    @if($application->status === 'withdrawn')<small class="muted">Esta postulación fue retirada. Su historial se conserva.</small>@endif
                </article>
            @empty
                <div class="panel empty-state"><x-icon name="file" :size="34" /><h2>{{ collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty() ? 'No hay coincidencias con estos filtros' : 'Aún no tienes postulaciones' }}</h2><p>Consulta otro estado o encuentra una oferta, revisa sus condiciones y envía tu CV para empezar.</p><a class="button" href="{{ route('jobs.index') }}">Encontrar empleo</a></div>
            @endforelse
            {{ $applications->links() }}
        </div>
    </div>
</section>
@endsection
