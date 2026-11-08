@extends('layouts.app')
@section('title', $job->title)
@section('content')
@php $isCandidate = auth()->check() && auth()->user()->active && auth()->user()->role === 'candidate'; @endphp
<section class="container section">
    <a href="{{ route('jobs.index') }}" class="back-link">← Volver a empleos</a>
    <header class="job-detail-heading">
        <div class="company-avatar company-avatar-lg">@if($job->company->logo_path)<img src="{{ route('companies.logo', $job->company) }}" alt="Logo de {{ $job->company->name }}">@else{{ mb_strtoupper(mb_substr($job->company->name, 0, 1)) }}@endif</div>
        <div><div class="actions">
            <a class="text-link" href="{{ route('companies.show', $job->company) }}">{{ $job->company->name }}</a>
            @if($job->company->verified)<span class="badge badge-success"><x-icon name="shield" :size="15" />{{ $job->is_demo ? 'Verificación de ejemplo' : 'Empresa verificada' }}</span>@endif
            @if($job->is_demo)<span class="badge badge-demo">Demo · oferta ficticia</span>@endif
        </div><h1>{{ $job->title }}</h1><p><x-icon name="pin" :size="18" />{{ $job->district->name }}, Cusco <span>·</span> Publicado {{ $job->created_at->locale('es')->diffForHumans() }}</p></div>
    </header>
    @if($job->is_demo)<div class="demo-notice"><x-icon name="book" :size="20" /><span>Esta oferta es un <strong>ejemplo de demostración</strong> del proyecto. No representa una vacante real.</span></div>@endif
    @if($job->status === 'draft')<div class="alert alert-warning"><x-icon name="eye" /><span>Estás viendo un borrador privado. La oferta aparecerá en el buscador cuando se publique.</span></div>@endif
    <div class="detail-layout">
        <div class="stack">
            <article class="panel job-description">
                <div class="job-facts">
                    <div><x-icon name="briefcase" :size="23" /><span>Jornada<strong>{{ \App\Models\JobOffer::SCHEDULES[$job->schedule] ?? $job->schedule }}</strong></span></div>
                    <div><x-icon name="book" :size="23" /><span>Contrato<strong>{{ \App\Models\JobOffer::CONTRACT_TYPES[$job->contract_type] ?? $job->contract_type }}</strong></span></div>
                    <div><x-icon name="users" :size="23" /><span>Vacantes<strong>{{ $job->vacancies }}</strong></span></div>
                </div>
                <h2>Sobre la oportunidad</h2><div class="prose-content">{{ $job->description }}</div>
                <h2>Lo que buscamos</h2><div class="prose-content">{{ $job->requirements }}</div>
                <h2>Condiciones laborales</h2><div class="prose-content">{{ $job->conditions }}</div>
                <div class="job-tags"><span>{{ $job->category->name }}</span><span>{{ $job->district->name }}</span></div>
            </article>
            <details class="panel report-panel" @if($errors->has('reason') || $errors->has('details')) open @endif>
                <summary><x-icon name="shield" :size="20" />¿Esta oferta te parece sospechosa?</summary>
                <p>Ayúdanos a cuidar la comunidad. Tu reporte llegará al equipo de administración para su revisión.</p>
                @if(auth()->check() && auth()->user()->active)
                    <form action="{{ route('reports.store', $job) }}" method="POST" class="stack">
                        @csrf
                        <div class="field"><label for="reason">Motivo del reporte</label><select id="reason" name="reason" required><option value="">Selecciona un motivo</option>@foreach(['Posible fraude', 'Contenido inapropiado', 'Información engañosa', 'Otro motivo'] as $reason)<option value="{{ $reason }}" @selected(old('reason') === $reason)>{{ $reason }}</option>@endforeach</select></div>
                        <div class="field"><label for="details">Cuéntanos qué ocurrió</label><textarea id="details" name="details" minlength="10" maxlength="3000" rows="4" required aria-describedby="report-help">{{ old('details') }}</textarea><small id="report-help">Incluye al menos 10 caracteres. Evita escribir contraseñas o datos sensibles.</small></div>
                        <div><button type="submit" class="button button-secondary">Enviar reporte <x-icon name="arrow" :size="18" /></button></div>
                    </form>
                @else
                    <a class="text-link" href="{{ route('login') }}">Inicia sesión para reportar <x-icon name="arrow" :size="18" /></a>
                @endif
            </details>
        </div>
        <aside class="stack" aria-label="Postulación y resumen">
            <div class="panel application-panel">
                <span class="eyebrow">TU PRÓXIMO PASO</span>
                <div class="detail-salary">
                    @if($job->salary_min !== null)S/ {{ number_format($job->salary_min, 0) }}@if($job->salary_max !== null && $job->salary_max != $job->salary_min)<span> a S/ {{ number_format($job->salary_max, 0) }}</span>@endif<small>Remuneración mensual</small>
                    @else A convenir<small>Consulta las condiciones del puesto</small>@endif
                </div>
                @if($job->expires_at)<p class="muted"><x-icon name="clock" :size="18" />Cierre: {{ $job->expires_at->format('d/m/Y') }}</p>@endif
                @if($application && $isCandidate)
                    <div class="application-summary"><strong>Tu postulación está registrada</strong><x-application-status :status="$application->status" /><a class="text-link" href="{{ route('candidate.applications.index') }}">Ver seguimiento <x-icon name="arrow" :size="17" /></a></div>
                @endif
                @if(!$job->isOpen())
                    <div class="alert alert-warning"><span>{{ $job->status === 'draft' ? 'Esta oferta aún no está publicada.' : ($job->status === 'closed' ? 'Esta convocatoria está cerrada.' : 'Esta convocatoria está cerrada. El plazo para postular terminó.') }}</span></div>
                @elseif($isCandidate)
                    @unless($application)
                        <form action="{{ route('applications.store', $job) }}" method="POST" enctype="multipart/form-data" class="stack">
                            @csrf
                            <div class="field"><label for="cv">Tu CV en PDF</label><input type="file" id="cv" name="cv" accept="application/pdf,.pdf" aria-describedby="cv-help"><small id="cv-help">Hasta 5 MB. Si ya tienes un CV en tu perfil, puedes utilizarlo sin volver a adjuntarlo.</small>@error('cv')<span class="field-error">{{ $message }}</span>@enderror</div>
                            <div class="field"><label for="cover_letter">Preséntate brevemente <span class="muted">(opcional)</span></label><textarea id="cover_letter" name="cover_letter" rows="4" maxlength="3000" placeholder="Cuéntales por qué te interesa esta oportunidad.">{{ old('cover_letter') }}</textarea></div>
                            <button type="submit" class="button button-full">Enviar postulación <x-icon name="arrow" :size="19" /></button>
                        </form>
                    @endunless
                @elseif(auth()->check())
                    <p>Las postulaciones están disponibles para cuentas de candidato.</p><a class="button button-full" href="{{ route('dashboard') }}">Ir a mi panel <x-icon name="arrow" :size="19" /></a>
                @else
                    <a class="button button-full" href="{{ route('login') }}">Inicia sesión para postular <x-icon name="arrow" :size="19" /></a><a class="button button-secondary button-full" href="{{ route('register') }}">Crear cuenta gratis</a>
                @endif
                @if($isCandidate && ($job->isOpen() || $isSaved))
                    <form method="POST" action="{{ route('favorites.toggle', $job) }}">@csrf<button type="submit" class="button button-secondary button-full" aria-pressed="{{ $isSaved ? 'true' : 'false' }}"><x-icon name="bookmark" :size="19" />{{ $isSaved ? 'Quitar de guardados' : 'Guardar oportunidad' }}</button></form>
                @endif
                <span class="private-note"><x-icon name="lock" :size="18" />Tu CV solo se comparte con la empresa a la que postulas.</span>
            </div>
            <div class="panel company-summary"><span class="eyebrow">CONOCE AL EQUIPO</span><h3>{{ $job->company->name }}</h3><p>{{ \Illuminate\Support\Str::limit($job->company->description, 180) }}</p><a class="text-link" href="{{ route('companies.show', $job->company) }}">Ver perfil de empresa <x-icon name="arrow" :size="18" /></a></div>
            <div class="panel safety-card"><span class="category-icon"><x-icon name="shield" :size="27" /></span><h3>Tu seguridad, primero</h3><p>Nunca realices pagos para postular ni compartas tus claves personales. Reporta cualquier solicitud sospechosa.</p></div>
        </aside>
    </div>
    @if($related->isNotEmpty())
        <section class="section"><div class="section-heading"><div><span class="eyebrow">SIGUE EXPLORANDO</span><h2>Más caminos para ti</h2></div><a class="text-link" href="{{ route('jobs.index') }}">Todos los empleos <x-icon name="arrow" :size="18" /></a></div><div class="grid-3">@foreach($related as $relatedJob)<x-job-card :job="$relatedJob" />@endforeach</div></section>
    @endif
</section>
@endsection
