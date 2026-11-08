@extends('layouts.app')
@section('title', 'Administración')
@section('content')
<div class="container section dashboard-layout">
    @include('admin.sidebar')
    <div class="dashboard-main stack">
        <header class="page-heading"><div><span class="eyebrow">CENTRO DE ADMINISTRACIÓN</span><h1>Una comunidad que crece con confianza.</h1><p>Supervisa la actividad, revisa las empresas y cuida la calidad de las oportunidades.</p></div><a class="button button-secondary" href="{{ route('jobs.index') }}">Ver plataforma <x-icon name="arrow" :size="18" /></a></header>
        <div class="stats">@foreach($stats as $label => $value)<div class="stat panel"><span>{{ $label }}</span><strong>{{ number_format($value) }}</strong></div>@endforeach</div>
        <section class="panel stack" aria-labelledby="verification-heading">
            <div class="page-heading"><div><span class="eyebrow">REVISIÓN DE EMPRESAS</span><h2 id="verification-heading">Pendientes de verificación</h2><p>La verificación requiere revisar manualmente la identidad y los datos del negocio.</p></div><a class="text-link" href="{{ route('admin.content.index', ['section' => 'companies', 'verified' => 0]) }}">Ver pendientes <x-icon name="arrow" :size="18" /></a></div>
            @forelse($pendingCompanies ?? [] as $company)
                @php $complete = $company->name && $company->description && $company->sector && $company->address && $company->district_id; @endphp
                <article class="card verification-card"><div class="page-heading"><div><div class="actions"><span class="badge badge-warning">Pendiente</span>@if($company->is_demo)<span class="badge badge-demo">Empresa ficticia · Demo</span>@endif</div><h3>{{ $company->name }}</h3><p>{{ $company->sector ?: 'Rubro por completar' }} · {{ $complete ? 'Perfil completo para revisión' : 'Faltan datos del perfil empresarial' }}</p></div></div>
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
                <article class="card"><div class="actions"><span class="badge {{ $report->status === 'pending' ? 'badge-danger' : 'badge-success' }}">{{ \App\Models\Report::STATUSES[$report->status] ?? $report->status }}</span><span class="muted">{{ $report->created_at->format('d/m/Y') }}</span></div><h3>{{ $reason }}</h3><p>{{ $report->jobOffer->title }} · {{ $report->user->name }}</p><p>{{ \Illuminate\Support\Str::limit($report->details, 180) }}</p><a class="text-link" href="{{ route('admin.content.edit', ['section' => 'reports', 'record' => $report->id]) }}">Revisar reporte <x-icon name="arrow" :size="18" /></a></article>
            @empty
                <div class="empty-state"><x-icon name="check" :size="34" /><h3>No hay reportes por revisar</h3><p>Los reportes enviados desde una oferta aparecerán aquí.</p></div>
            @endforelse
        </section>
        <section aria-labelledby="management-heading"><div class="section-heading"><div><span class="eyebrow">GESTIÓN DE LA PLATAFORMA</span><h2 id="management-heading">Todo en su lugar</h2></div></div><div class="grid-3">@foreach($sections as $key => $definition)<a class="card stack" href="{{ route('admin.content.index', ['section' => $key]) }}"><span class="category-icon"><x-icon name="{{ match($key) { 'users' => 'users', 'companies' => 'building', 'resources' => 'book', 'reports' => 'shield', default => 'briefcase' } }}" :size="25" /></span><h3>{{ $definition[1] }}</h3><span class="text-link">Abrir gestión <x-icon name="arrow" :size="18" /></span></a>@endforeach</div></section>
    </div>
</div>
@endsection
