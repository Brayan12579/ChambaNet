@extends('layouts.app')
@section('title', 'Mis ofertas')
@section('content')
<section class="section container">
    <div class="page-heading"><div><p class="eyebrow">GESTIÓN DE PUBLICACIONES</p><h1>Mis ofertas laborales</h1><p class="muted">Crea, actualiza y revisa tus procesos de selección.</p></div><a class="button" href="{{ route('employer.jobs.create') }}">Publicar oferta +</a></div>
    <div class="dashboard-layout">@include('employer.sidebar')<div class="dashboard-main stack">
        <form method="GET" class="panel actions"><label for="status">Mostrar</label><select id="status" name="status"><option value="">Todos los estados</option>@foreach(\App\Models\JobOffer::STATUSES as $value => $label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select><button class="button-secondary">Filtrar</button></form>
        @forelse($jobs as $job)<article class="panel"><div class="page-heading"><div><span class="badge">{{ \App\Models\JobOffer::STATUSES[$job->status] }}</span>@if($job->expires_at?->isBefore(today())) <span class="badge warning">Vencida</span>@endif<h2>{{ $job->title }}</h2><p class="muted">{{ $job->district->name }} · {{ $job->category->name }} · {{ $job->applications_count }} postulaciones</p></div></div>
            <div class="actions"><a class="button" href="{{ route('employer.jobs.applicants', $job) }}">Ver postulaciones</a><a class="button-secondary" href="{{ route('employer.jobs.edit', $job) }}">Editar</a><a class="text-link" href="{{ route('jobs.show', $job) }}">Vista previa ↗</a>
                @if($job->status !== 'closed')<form method="POST" action="{{ route('employer.jobs.close', $job) }}" data-confirm="¿Cerrar esta oferta? Ya no recibirá nuevas postulaciones.">@csrf @method('PATCH')<button class="button-ghost">Cerrar oferta</button></form>@endif
                <form method="POST" action="{{ route('employer.jobs.destroy', $job) }}" data-confirm="¿Eliminar esta oferta? Solo se permite si no tiene postulaciones ni reportes.">@csrf @method('DELETE')<button class="button-danger">Eliminar</button></form>
            </div>
        </article>@empty<div class="empty-state panel"><h2>No hay ofertas en este estado</h2><p>Publica una oportunidad y conecta con candidatos de tu comunidad.</p><a class="button" href="{{ route('employer.jobs.create') }}">Crear oferta</a></div>@endforelse
        {{ $jobs->links() }}
    </div></div>
</section>
@endsection