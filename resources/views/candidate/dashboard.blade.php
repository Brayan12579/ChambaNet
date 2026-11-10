@extends('layouts.app')
@section('title', 'Mi espacio')
@section('content')
<section class="container section">
    <div class="page-heading workspace-heading"><div><span class="eyebrow">MI BÚSQUEDA DE EMPLEO</span><h1>Hola, {{ Str::before(auth()->user()->name, ' ') }}.</h1><p>Tu búsqueda, tus avances y tu próxima oportunidad.</p></div><a class="button" href="{{ route('jobs.index') }}"><x-icon name="search" :size="19" />Explorar {{ $availableCount }} empleos</a></div>
    <div class="dashboard-layout">
        @include('candidate._nav')
        <div class="dashboard-main stack">
            <div class="stats grid grid-3">
                <a class="stat panel" href="{{ route('candidate.applications.index') }}"><span><x-icon name="file" :size="19" />Postulaciones enviadas</span><strong>{{ $applicationCounts->sum() }}</strong><small>Consultar mis solicitudes →</small></a>
                <a class="stat panel" href="{{ route('candidate.applications.index', ['status' => 'reviewing']) }}"><span><x-icon name="eye" :size="19" />En revisión</span><strong>{{ $applicationCounts['reviewing'] ?? 0 }}</strong><small>Las empresas revisan tu perfil →</small></a>
                <a class="stat panel" href="{{ route('candidate.favorites.index') }}"><span><x-icon name="bookmark" :size="19" />Empleos guardados</span><strong>{{ $savedCount }}</strong><small>Retomar mi búsqueda →</small></a>
            </div>
            <form class="panel dashboard-search" action="{{ route('jobs.index') }}" method="GET" role="search"><div class="field"><label for="candidate-search">¿Cuál será tu próximo empleo?</label><input id="candidate-search" type="search" name="q" maxlength="120" placeholder="Puesto, empresa o palabra clave"></div><button class="button" type="submit"><x-icon name="search" :size="18" />Buscar empleo</button></form>
            <x-profile-completion :completion="$completion" :compact="true" />
            <div class="panel">
                <div class="page-heading"><div><span class="eyebrow">SIGUE TU AVANCE</span><h2>Últimas postulaciones</h2></div><a class="text-link" href="{{ route('candidate.applications.index') }}">Ver todas →</a></div>
                @forelse($applications as $application)
                    <article class="list-row"><div><h3><a class="text-link" href="{{ route('candidate.applications.index', ['q' => $application->jobOffer->title]) }}">{{ $application->jobOffer->title }}</a></h3><p>{{ $application->jobOffer->company->name }} · {{ $application->created_at->format('d/m/Y') }}</p></div><x-application-status :status="$application->status" /></article>
                @empty
                    <div class="empty-state"><h3>Tu próxima oportunidad te espera</h3><p>Aún no enviaste postulaciones. Explora los empleos y encuentra uno que se ajuste a ti.</p><a class="button" href="{{ route('jobs.index') }}">Explorar empleos</a></div>
                @endforelse
            </div>
            <section aria-labelledby="candidate-opportunities"><div class="page-heading"><div><span class="eyebrow">CONTINÚA TU BÚSQUEDA</span><h2 id="candidate-opportunities">Oportunidades para explorar</h2><p>Ofertas a las que aún no postulaste; primero las de tu distrito.</p></div><a class="text-link" href="{{ route('jobs.index') }}">Ver empleos →</a></div><div class="grid-3 dashboard-jobs">@forelse($suggestedJobs as $job)<x-job-card :job="$job" />@empty<div class="panel empty-state"><h3>No hay nuevas ofertas por ahora</h3><p>Puedes consultar tus postulaciones o volver más tarde para descubrir nuevas publicaciones.</p></div>@endforelse</div></section>
        </div>
    </div>
</section>
@endsection
