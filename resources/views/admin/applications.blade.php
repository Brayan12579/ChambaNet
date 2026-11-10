@extends('layouts.app')
@section('title', 'Supervisión de postulaciones')
@section('content')
<div class="container section dashboard-layout">
    @include('admin.sidebar')
    <div class="dashboard-main stack">
        <header class="page-heading"><div><span class="eyebrow">SUPERVISIÓN</span><h1>Postulaciones</h1><p class="muted">Revisa la actividad y el estado de los procesos de selección.</p></div><span class="badge">{{ number_format($applications->total()) }} registros</span></header>
        <form class="panel stack" method="GET" action="{{ route('admin.applications') }}" role="search"><div class="form-grid"><div class="field"><label for="admin-application-search">Buscar proceso</label><input type="search" id="admin-application-search" name="q" maxlength="160" value="{{ $filters['q'] ?? '' }}" placeholder="Candidato, correo, oferta o empresa"></div><div class="field"><label for="admin-application-status">Estado</label><select id="admin-application-status" name="status"><option value="">Todos los estados</option>@foreach(\App\Models\Application::STATUSES as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div></div><div class="actions"><button class="button-secondary" type="submit"><x-icon name="search" :size="18" />Buscar postulaciones</button>@if(collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty())<a class="text-link" href="{{ route('admin.applications') }}">Limpiar filtros</a>@endif</div></form>
        @if($applications->isNotEmpty())
            <div class="panel table-wrap" tabindex="0" role="region" aria-label="Postulaciones, tabla desplazable"><table><caption class="sr-only">Supervisión de postulaciones</caption><thead><tr><th scope="col">Candidato</th><th scope="col">Oferta y empresa</th><th scope="col">Estado</th><th scope="col">Fecha</th></tr></thead><tbody>
                @foreach($applications as $application)<tr><td><strong>{{ $application->user->name }}</strong></td><td><a class="text-link" href="{{ route('admin.content.edit', ['section' => 'jobs', 'record' => $application->job_offer_id]) }}">{{ $application->jobOffer->title }}</a><div class="muted">{{ $application->jobOffer->company->name }}</div></td><td><x-application-status :status="$application->status" /></td><td class="muted">{{ $application->created_at->format('d/m/Y') }}</td></tr>@endforeach
            </tbody></table></div>
            <div class="pagination">{{ $applications->links() }}</div>
        @else
            <div class="panel empty-state"><x-icon name="users" :size="36" /><h2>No hay postulaciones para mostrar</h2><p class="muted">Revisa los filtros o vuelve cuando se hayan registrado nuevas postulaciones.</p></div>
        @endif
    </div>
</div>
@endsection
