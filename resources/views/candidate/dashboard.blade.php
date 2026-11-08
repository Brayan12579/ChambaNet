@extends('layouts.app')
@section('title', 'Mi espacio')
@section('content')
<section class="container section">
    <div class="page-heading"><div><span class="eyebrow">TU PRÓXIMO PASO EMPIEZA AQUÍ</span><h1>Hola, {{ Str::before(auth()->user()->name, ' ') }}.</h1><p class="muted">Encuentra una oportunidad cerca de ti y sigue cada postulación.</p></div></div>
    <div class="dashboard-layout">
        @include('candidate._nav')
        <div class="dashboard-main stack">
            <div class="stats grid grid-3">
                <div class="stat panel"><span class="muted">Postulaciones enviadas</span><strong>{{ $applicationCounts->sum() }}</strong></div>
                <div class="stat panel"><span class="muted">En proceso</span><strong>{{ ($applicationCounts['pending'] ?? 0) + ($applicationCounts['reviewing'] ?? 0) }}</strong></div>
                <div class="stat panel"><span class="muted">Empleos guardados</span><strong>{{ $savedCount }}</strong></div>
            </div>
            <x-profile-completion :completion="$completion" />
            <div class="panel"><div class="page-heading"><div><h2>Tu perfil es tu presentación</h2><p class="muted">{{ $profile?->cv_path ? 'Tu CV está listo para adjuntarlo a tus próximas postulaciones.' : 'Agrega tu experiencia y sube tu CV en PDF para empezar a postular.' }}</p></div><a class="button button-secondary" href="{{ route('candidate.profile.edit') }}">{{ $profile?->cv_path ? 'Editar mi perfil' : 'Completar mi perfil' }}</a></div></div>
            <div class="panel">
                <div class="page-heading"><h2>Últimas postulaciones</h2><a class="text-link" href="{{ route('candidate.applications.index') }}">Ver todas →</a></div>
                @forelse($applications as $application)
                    <article class="card"><div class="page-heading"><div><h3><a href="{{ route('jobs.show', $application->jobOffer) }}">{{ $application->jobOffer->title }}</a></h3><p class="muted">{{ $application->jobOffer->company->name }} · {{ $application->jobOffer->district->name }}</p></div><x-application-status :status="$application->status" /></div><small class="muted">Enviada el {{ $application->created_at->format('d/m/Y') }}</small></article>
                @empty
                    <div class="empty-state"><h3>Tu próxima oportunidad te espera</h3><p>Aún no enviaste postulaciones. Explora los empleos y encuentra uno que se ajuste a ti.</p><a class="button" href="{{ route('jobs.index') }}">Explorar empleos</a></div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
