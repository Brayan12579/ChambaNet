@extends('layouts.app')
@section('title', 'Administración')
@section('content')
<div class="container section dashboard-layout">
    @include('admin.sidebar')
    <div class="dashboard-main stack">
        <header class="page-heading workspace-heading"><div><span class="eyebrow">CENTRO DE ADMINISTRACIÓN</span><h1>La plataforma, de un vistazo.</h1><p>Actividad real y tareas que necesitan tu atención.</p></div><a class="button button-secondary" href="{{ route('home') }}">Ver plataforma <x-icon name="arrow" :size="18" /></a></header>
        @php
            $statLinks = [
                'Usuarios' => [route('admin.content.index', ['section' => 'users']), 'users', 'Gestionar cuentas'],
                'Empresas' => [route('admin.content.index', ['section' => 'companies']), 'building', 'Revisar empresas'],
                'Ofertas publicadas' => [route('admin.content.index', ['section' => 'jobs', 'status' => 'published']), 'briefcase', 'Ver ofertas vigentes'],
                'Postulaciones' => [route('admin.applications'), 'file', 'Supervisar procesos'],
                'Reportes pendientes' => [route('admin.content.index', ['section' => 'reports', 'status' => 'pending']), 'shield', 'Revisar reportes'],
                'Empresas por verificar' => [route('admin.content.index', ['section' => 'companies', 'verified' => 0]), 'clock', 'Abrir pendientes'],
            ];
        @endphp
        <div class="stats admin-stats">@foreach($stats as $label => $value)<a class="stat panel" href="{{ $statLinks[$label][0] }}"><span><x-icon :name="$statLinks[$label][1]" :size="19" />{{ $label }}</span><strong>{{ number_format($value) }}</strong><small>{{ $statLinks[$label][2] }} →</small></a>@endforeach</div>
        <div class="priority-strip"><x-icon name="shield" :size="23" /><div><strong>{{ $stats['Empresas por verificar'] + $stats['Reportes pendientes'] }} tareas pendientes</strong><p>Prioriza las verificaciones de empresas y la revisión de reportes.</p></div><a class="text-link" href="#verification-heading">Ir a revisión <x-icon name="arrow-down" :size="18" /></a></div>
        <section class="panel stack" aria-labelledby="verification-heading">
            <div class="page-heading"><div><span class="eyebrow">REVISIÓN DE EMPRESAS</span><h2 id="verification-heading">Pendientes de verificación</h2><p>La verificación requiere revisar manualmente la identidad y los datos del negocio.</p></div><a class="text-link" href="{{ route('admin.content.index', ['section' => 'companies', 'verified' => 0]) }}">Ver pendientes <x-icon name="arrow" :size="18" /></a></div>
            @forelse($pendingCompanies ?? [] as $company)
                @php $complete = $company->name && $company->description && $company->sector && $company->address && $company->district_id; @endphp
                <article class="verification-card review-row"><div><div class="actions"><span class="badge badge-warning">Pendiente</span>@if($company->is_demo)<span class="badge badge-demo">Empresa ficticia · Demo</span>@endif</div><h3>{{ $company->name }}</h3><p>{{ $company->sector ?: 'Rubro por completar' }} · {{ $company->district?->name ?? 'Sin distrito' }}</p><small>{{ $complete ? 'Perfil completo para revisión' : 'Faltan datos del perfil empresarial' }}</small></div>
                    <div class="actions"><a class="button button-secondary" href="{{ route('admin.content.edit', ['section' => 'companies', 'record' => $company->id]) }}">Revisar información</a>@if($complete)<form action="{{ route('admin.companies.verify', $company) }}" method="POST" data-confirm="¿Verificar a {{ $company->name }}? Confirma que revisaste manualmente los datos y la identidad de esta empresa.">@csrf @method('PATCH')<button class="button" type="submit"><x-icon name="shield" :size="18" />Verificar empresa</button></form>@endif</div>
                </article>
            @empty
                <div class="empty-state"><x-icon name="shield" :size="34" /><h3>La revisión está al día</h3><p>No hay empresas activas pendientes de verificación.</p></div>
            @endforelse
        </section>
        <section class="panel stack" aria-labelledby="reports-heading">
            <div class="page-heading"><div><span class="eyebrow">MODERACIÓN PRIORITARIA</span><h2 id="reports-heading">Reportes para revisar</h2><p>Investiga el contexto antes de decidir sobre una publicación.</p></div><a class="text-link" href="{{ route('admin.content.index', ['section' => 'reports', 'status' => 'pending']) }}">Todos los reportes <x-icon name="arrow" :size="18" /></a></div>
            @forelse($reports as $report)
                @php $reason = match($report->reason) { 'fraud' => 'Posible fraude', 'inappropriate' => 'Contenido inapropiado', 'misleading' => 'Información engañosa', 'other' => 'Otro motivo', default => $report->reason }; @endphp
                <article class="review-row"><div><div class="actions"><span class="badge badge-danger">Pendiente</span><time class="muted" datetime="{{ $report->created_at->toDateString() }}">{{ $report->created_at->format('d/m/Y') }}</time></div><h3>{{ $reason }}</h3><p>{{ $report->jobOffer->title }} · {{ $report->user->name }}</p><p>{{ \Illuminate\Support\Str::limit($report->details, 180) }}</p></div><a class="button button-secondary button-sm" href="{{ route('admin.content.edit', ['section' => 'reports', 'record' => $report->id]) }}">Revisar reporte <x-icon name="arrow" :size="18" /></a></article>
            @empty
                <div class="empty-state"><x-icon name="check" :size="34" /><h3>No hay reportes por revisar</h3><p>Los reportes enviados desde una oferta aparecerán aquí.</p></div>
            @endforelse
        </section>
        <section aria-labelledby="management-heading"><div class="section-heading"><div><span class="eyebrow">CONFIGURA LA OFERTA LOCAL</span><h2 id="management-heading">Catálogos y orientación</h2></div></div><div class="grid-3">@foreach(['categories' => ['sliders', 'Organiza los sectores laborales.'], 'districts' => ['pin', 'Administra las zonas de búsqueda.'], 'resources' => ['book', 'Publica orientación para candidatos.']] as $key => [$icon, $description])<a class="card management-card" href="{{ route('admin.content.index', ['section' => $key]) }}"><span class="category-icon"><x-icon :name="$icon" :size="23" /></span><h3>{{ $sections[$key][1] }}</h3><p>{{ $description }}</p><span class="text-link">Gestionar <x-icon name="arrow" :size="18" /></span></a>@endforeach</div></section>
    </div>
</div>
@endsection
