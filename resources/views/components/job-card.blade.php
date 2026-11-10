@props(['job'])
@php
    $schedules = \App\Models\JobOffer::SCHEDULES;
    $saved = (bool) ($job->is_saved ?? false);
@endphp
<article class="job-card">
    <div class="job-card-top">
        <a class="company-avatar" href="{{ route('companies.show', $job->company) }}" aria-label="Conocer {{ $job->company->name }}">
            @if($job->company->logo_path)<img src="{{ route('companies.logo', $job->company) }}" alt="" loading="lazy">@else{{ mb_strtoupper(mb_substr($job->company->name, 0, 1)) }}@endif
        </a>
        <div class="job-company">
            <a href="{{ route('companies.show', $job->company) }}">{{ $job->company->name }}</a>
            <span><x-icon name="pin" :size="15" />{{ $job->district->name }}, Cusco</span>
        </div>
        @if(auth()->check() && auth()->user()->active && auth()->user()->role === 'candidate')
            <form action="{{ route('favorites.toggle', $job) }}" method="POST" class="job-save-form">
                @csrf
                <button type="submit" @class(['save-job-button', 'is-saved' => $saved]) aria-pressed="{{ $saved ? 'true' : 'false' }}" aria-label="{{ $saved ? 'Quitar de guardados: ' : 'Guardar oferta: ' }}{{ $job->title }}" title="{{ $saved ? 'Quitar de guardados' : 'Guardar oferta' }}">
                    <x-icon name="bookmark" :size="21" />
                </button>
            </form>
        @endif
    </div>
    @if($job->is_demo || $job->company->verified || $job->featured)<div class="job-card-badges">
        @if($job->is_demo)<span class="badge badge-demo">Demo · oferta ficticia</span>@endif
        @if($job->company->verified)<span class="badge badge-success"><x-icon name="shield" :size="14" />{{ $job->is_demo ? 'Verificación de ejemplo' : 'Empresa verificada' }}</span>@endif
        @if($job->featured)<span class="badge badge-featured"><x-icon name="spark" :size="14" />Destacada</span>@endif
    </div>@endif
    <h3><a href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a></h3>
    <div class="job-tags"><span>{{ $schedules[$job->schedule] ?? $job->schedule }}</span><span>{{ $job->category->name }}</span></div>
    <div class="job-contract"><x-icon name="file" :size="16" /><span>{{ \App\Models\JobOffer::CONTRACT_TYPES[$job->contract_type] ?? $job->contract_type }}</span><span>·</span><span>{{ $job->vacancies }} {{ $job->vacancies === 1 ? 'vacante' : 'vacantes' }}</span></div>
    <p class="job-excerpt">{{ \Illuminate\Support\Str::limit($job->description, 140) }}</p>
    <div class="job-salary">
        @if($job->salary_min !== null)
            <strong>S/ {{ number_format($job->salary_min, 0) }}@if($job->salary_max !== null && $job->salary_max != $job->salary_min) – {{ number_format($job->salary_max, 0) }}@endif</strong><span>/ mes</span>
        @else
            <strong>A convenir</strong>
        @endif
    </div>
    <div class="job-card-bottom">
        <time datetime="{{ $job->created_at->toDateString() }}"><x-icon name="clock" :size="15" />{{ $job->created_at->locale('es')->diffForHumans() }}</time>
        <a href="{{ route('jobs.show', $job) }}" class="button button-secondary button-sm">Ver oferta <x-icon name="arrow" :size="16" /></a>
    </div>
</article>
