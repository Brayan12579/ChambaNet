@extends('layouts.app')
@section('title', 'Empleos guardados')
@section('content')
<section class="container section">
    <div class="page-heading"><div><span class="eyebrow">OPORTUNIDADES QUE TE INTERESAN</span><h1>Empleos guardados</h1><p class="muted">Vuelve a las ofertas que llamaron tu atención.</p></div></div>
    <div class="dashboard-layout">
        @include('candidate._nav')
        <div class="dashboard-main stack">
            @forelse($jobs as $job)
                <article class="panel stack">
                    <div class="page-heading"><div><p class="muted">{{ $job->company->name }} · {{ $job->district->name }}</p><h2><a href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a></h2></div><span class="badge">{{ $job->isOpen() ? 'Disponible' : 'Convocatoria cerrada' }}</span></div>
                    <p>{{ Str::limit($job->description, 180) }}</p>
                    <div class="actions"><span class="badge">{{ \App\Models\JobOffer::SCHEDULES[$job->schedule] ?? $job->schedule }}</span>@if($job->salary_min)<strong>S/ {{ number_format($job->salary_min, 0) }}@if($job->salary_max) – {{ number_format($job->salary_max, 0) }}@endif</strong>@endif @if($job->is_demo)<span class="badge">Demostración</span>@endif</div>
                    <div class="actions"><a class="button button-secondary" href="{{ route('jobs.show', $job) }}">Ver oferta</a><form method="POST" action="{{ route('favorites.toggle', $job) }}">@csrf<button class="button button-ghost" type="submit">Quitar de guardados</button></form></div>
                </article>
            @empty
                <div class="panel empty-state"><h2>Guarda tus próximas oportunidades</h2><p>Utiliza el botón de guardar en una oferta para encontrarla aquí cuando vuelvas.</p><a class="button" href="{{ route('jobs.index') }}">Explorar empleos</a></div>
            @endforelse
            {{ $jobs->links() }}
        </div>
    </div>
</section>
@endsection
