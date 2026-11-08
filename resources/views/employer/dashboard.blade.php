@extends('layouts.app')
@section('title', 'Panel de empresa')
@section('content')
<section class="section container">
    <div class="page-heading"><div><p class="eyebrow">TU NEGOCIO, CON MÁS POSIBILIDADES</p><h1>Hola, {{ auth()->user()->name }}.</h1><p class="muted">Encuentra a las personas que harán crecer tu equipo.</p></div><a class="button" href="{{ route('employer.jobs.create') }}">Publicar oferta +</a></div>
    <div class="dashboard-layout">
        @include('employer.sidebar')
        <div class="dashboard-main stack">
            <div class="stats">@foreach($stats as $label => $count)<div class="stat card"><strong>{{ $count }}</strong><span>{{ $label }}</span></div>@endforeach</div>
            @unless($company->description && $company->district_id && $company->address)
                <div class="alert"><strong>Presenta tu empresa.</strong> Completa tu perfil para comenzar a publicar. <a class="text-link" href="{{ route('employer.company.edit') }}">Completar perfil →</a></div>
            @endunless
            <div class="panel"><div class="page-heading"><h2>Actividad de tus ofertas</h2><a class="text-link" href="{{ route('employer.jobs.index') }}">Ver todas →</a></div>
                @forelse($jobs as $job)<article class="card"><div class="page-heading"><div><span class="badge">{{ \App\Models\JobOffer::STATUSES[$job->status] }}</span><h3>{{ $job->title }}</h3><p class="muted">{{ $job->applications_count }} postulaciones · Cierre {{ $job->expires_at?->format('d/m/Y') ?? 'Sin fecha' }}</p></div><a class="button-secondary" href="{{ route('employer.jobs.applicants', $job) }}">Revisar candidatos</a></div></article>
                @empty<div class="empty-state"><h3>Tu próxima contratación empieza aquí</h3><p>Crea tu primera oferta con los requisitos, la jornada y las condiciones del puesto.</p><a class="button" href="{{ route('employer.jobs.create') }}">Crear primera oferta</a></div>@endforelse
            </div>
            <div class="panel"><h3>Publicar es gratuito en este prototipo</h3><p class="muted">La estructura de planes está preparada para una siguiente etapa. No se procesan pagos ni se solicitan tarjetas.</p></div>
        </div>
    </div>
</section>
@endsection