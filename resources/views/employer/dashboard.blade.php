@extends('layouts.app')
@section('title', 'Panel de empresa')
@section('content')
<section class="section container">
    <div class="page-heading workspace-heading"><div><span class="eyebrow">CENTRO DE CONTRATACIÓN</span><h1>El próximo talento de tu equipo.</h1><p>{{ $company->name }} · Organiza tus vacantes y responde a tus candidatos.</p></div><a class="button" href="{{ route('employer.jobs.create') }}"><x-icon name="briefcase" :size="19" />Publicar oferta</a></div>
    <div class="dashboard-layout">
        @include('employer.sidebar')
        <div class="dashboard-main stack">
            <div class="stats recruitment-stats">
                <a class="stat panel" href="{{ route('employer.applications.index', ['status' => 'pending']) }}"><span><x-icon name="clock" :size="19" />Candidatos por revisar</span><strong>{{ $stats['Por revisar'] }}</strong><small>Abrir postulaciones pendientes →</small></a>
                <a class="stat panel" href="{{ route('employer.jobs.index', ['status' => 'published']) }}"><span><x-icon name="briefcase" :size="19" />Ofertas activas</span><strong>{{ $stats['Ofertas activas'] }}</strong><small>Gestionar publicaciones →</small></a>
                <a class="stat panel" href="{{ route('employer.applications.index') }}"><span><x-icon name="users" :size="19" />Postulaciones recibidas</span><strong>{{ $stats['Postulaciones recibidas'] }}</strong><small>Ver todos los candidatos →</small></a>
            </div>
            @if($companyMissingFields)
                <div class="alert"><x-icon name="building" :size="24" /><div><strong>Tu empresa necesita una presentación completa.</strong><p>Falta: {{ implode(', ', $companyMissingFields) }}.</p><a class="text-link" href="{{ route('employer.company.edit') }}">Completar perfil para publicar →</a></div></div>
            @endif
            <div class="panel"><div class="page-heading"><div><span class="eyebrow">TUS PROCESOS DE SELECCIÓN</span><h2>Actividad de tus ofertas</h2></div><a class="text-link" href="{{ route('employer.jobs.index') }}">Ver todas →</a></div>
                <div class="status-shortcuts"><a href="{{ route('employer.jobs.index', ['status' => 'draft']) }}">{{ $stats['Borradores'] }} borradores</a><a href="{{ route('employer.jobs.index', ['status' => 'expired']) }}">{{ $stats['Ofertas vencidas'] }} vencidas</a><a href="{{ route('employer.jobs.index', ['status' => 'closed']) }}">{{ $stats['Ofertas cerradas'] }} cerradas</a></div>
                @forelse($jobs as $job)<article class="list-row"><div><span class="badge {{ $job->status === 'published' && $job->expires_at?->isBefore(today()) ? 'badge-warning' : '' }}">{{ $job->status === 'published' && $job->expires_at?->isBefore(today()) ? 'Vencida' : \App\Models\JobOffer::STATUSES[$job->status] }}</span><h3>{{ $job->title }}</h3><p>{{ $job->applications_count }} postulaciones · {{ $job->pending_count }} por revisar · {{ $job->expires_at ? 'Cierre '.$job->expires_at->format('d/m/Y') : 'Sin fecha de cierre' }}</p></div><div class="actions"><a class="button button-secondary button-sm" href="{{ route('employer.jobs.applicants', $job) }}">Ver candidatos</a><a class="text-link" href="{{ route('employer.jobs.edit', $job) }}">Editar</a></div></article>
                @empty<div class="empty-state"><h3>Tu próxima contratación empieza aquí</h3><p>Crea tu primera oferta con los requisitos, la jornada y las condiciones del puesto.</p><a class="button" href="{{ route('employer.jobs.create') }}">Crear primera oferta</a></div>@endforelse
            </div>
            <div class="dashboard-columns">
                <section class="panel"><div class="page-heading"><div><span class="eyebrow">NUEVAS CONVERSACIONES</span><h2>Últimos postulantes</h2></div><a class="text-link" href="{{ route('employer.applications.index') }}">Ver todos →</a></div>@forelse($recentApplications as $application)<article class="list-row"><div><h3>{{ $application->user->name }}</h3><a class="text-link" href="{{ route('employer.jobs.applicants', $application->jobOffer) }}">{{ $application->jobOffer->title }}</a><p>{{ $application->created_at->format('d/m/Y') }}</p></div><x-application-status :status="$application->status" /></article>@empty<p>Los candidatos aparecerán aquí cuando postulen a tus ofertas.</p>@endforelse</section>
                <section class="panel company-summary"><span class="company-avatar company-avatar-lg">{{ mb_strtoupper(mb_substr($company->name, 0, 1)) }}</span><h2>{{ $company->name }}</h2><span class="badge {{ $company->verified ? 'badge-success' : 'badge-warning' }}">{{ $company->verified ? 'Empresa verificada' : 'Pendiente de verificación' }}</span><p>{{ $company->sector ?: 'Agrega el rubro de tu negocio' }}</p><p>{{ Str::limit($company->description, 160) ?: 'Presenta tu negocio para que los candidatos conozcan a su próximo equipo.' }}</p><a class="button button-secondary" href="{{ route('employer.company.edit') }}">Editar perfil de empresa</a></section>
            </div>
        </div>
    </div>
</section>
@endsection
