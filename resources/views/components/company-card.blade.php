@props(['company'])
<a class="company-card" href="{{ route('companies.show', $company) }}">
    <span class="company-avatar company-avatar-lg">@if($company->logo_path)<img src="{{ route('companies.logo', $company) }}" alt="" loading="lazy">@else{{ mb_strtoupper(mb_substr($company->name, 0, 1)) }}@endif</span>
    <h3>{{ $company->name }}</h3>
    <p>{{ $company->sector ?: 'Negocio local' }}@if($company->district) · {{ $company->district->name }}@endif</p>
    <div class="company-card-badges">@if($company->is_demo)<span class="badge badge-demo">Empresa ficticia · Demo</span>@elseif($company->verified)<span class="badge badge-success"><x-icon name="shield" :size="15" />Verificada</span>@endif</div>
    <span class="company-vacancies">{{ $company->job_offers_count }} {{ $company->job_offers_count === 1 ? 'empleo disponible' : 'empleos disponibles' }} <x-icon name="arrow" :size="18" /></span>
</a>
