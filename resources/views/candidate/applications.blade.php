@extends('layouts.app')
@section('title', 'Mis postulaciones')
@section('content')
<section class="container section">
    <div class="page-heading"><div><span class="eyebrow">CADA PASO CUENTA</span><h1>Mis postulaciones</h1><p class="muted">Consulta tus solicitudes y las respuestas de cada empresa.</p></div><a class="button button-secondary" href="{{ route('jobs.index') }}">Buscar más empleos</a></div>
    <div class="dashboard-layout">
        @include('candidate._nav')
        <div class="dashboard-main stack">
            @forelse($applications as $application)
                <article class="panel stack">
                    <div class="page-heading"><div><h2><a href="{{ route('jobs.show', $application->jobOffer) }}">{{ $application->jobOffer->title }}</a></h2><p class="muted">{{ $application->jobOffer->company->name }} · {{ $application->jobOffer->district->name }}</p></div><x-application-status :status="$application->status" /></div>
                    <x-application-status :status="$application->status" :timeline="true" :show-badge="false" />
                    <p class="muted">Postulaste el {{ $application->created_at->format('d/m/Y \a \l\a\s H:i') }}.</p>
                    @if($application->cover_letter)<p>{{ $application->cover_letter }}</p>@endif
                    <div class="actions">
                        <a class="button button-secondary" href="{{ route('applications.cv', $application) }}" target="_blank" rel="noopener">Ver CV enviado ↗</a>
                        @if(in_array($application->status, ['pending', 'reviewing']))
                            <form method="POST" action="{{ route('candidate.applications.withdraw', $application) }}" data-confirm="¿Retirar esta postulación? No podrás volver a postular a esta misma oferta.">@csrf @method('PATCH')<button class="button button-ghost" type="submit">Retirar postulación</button></form>
                        @endif
                    </div>
                    @if($application->status === 'withdrawn')<small class="muted">Esta postulación fue retirada. Su historial se conserva.</small>@endif
                </article>
            @empty
                <div class="panel empty-state"><h2>Aún no tienes postulaciones</h2><p>Encuentra una oferta, revisa sus condiciones y envía tu CV para empezar.</p><a class="button" href="{{ route('jobs.index') }}">Encontrar empleo</a></div>
            @endforelse
            {{ $applications->links() }}
        </div>
    </div>
</section>
@endsection
