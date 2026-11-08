@extends('layouts.app')
@section('title', $company->name)
@section('content')
<section class="page-hero">
    <div class="container">
        <a class="back-link" href="{{ route('companies.index') }}">← Todas las empresas</a>
        <div class="company-profile-heading">
            <div class="company-avatar company-avatar-lg">@if($company->logo_path)<img src="{{ route('companies.logo', $company) }}" alt="Logo de {{ $company->name }}">@else{{ mb_strtoupper(mb_substr($company->name, 0, 1)) }}@endif</div>
            <div><span class="eyebrow">{{ $company->sector ?: 'NEGOCIO LOCAL' }}</span><h1>{{ $company->name }}</h1><div class="actions"><span class="company-location"><x-icon name="pin" :size="19" />{{ $company->district?->name ?? 'Cusco' }}, Perú</span>@if($company->is_demo)<span class="badge badge-demo">Empresa ficticia · Demo</span>@elseif($company->verified)<span class="badge badge-success"><x-icon name="shield" :size="15" />Empresa verificada</span>@endif</div></div>
        </div>
    </div>
</section>
<section class="container section detail-layout">
    <div class="stack">
        @if($company->is_demo)<div class="demo-notice"><x-icon name="book" :size="20" /><span>Este es un perfil de demostración académica. La empresa y sus ofertas son ficticias.</span></div>@endif
        <article class="panel company-about">
            <span class="eyebrow">CONOCE A TU PRÓXIMO EQUIPO</span><h2>Sobre {{ $company->name }}</h2>
            <div class="prose-content">{{ $company->description ?: 'Esta empresa está preparando su presentación.' }}</div>
            @if($company->address)<p class="company-address"><x-icon name="pin" :size="19" />{{ $company->address }}</p>@endif
            @if($company->website && filter_var($company->website, FILTER_VALIDATE_URL) && in_array(parse_url($company->website, PHP_URL_SCHEME), ['http', 'https']))
                <a class="text-link" href="{{ $company->website }}" target="_blank" rel="noopener noreferrer">Visitar sitio de la empresa <x-icon name="arrow" :size="18" /></a>
            @endif
        </article>
        <div class="section-heading company-jobs-heading"><div><span class="eyebrow">ÚNETE AL EQUIPO</span><h2>Oportunidades abiertas <span class="badge">{{ $jobs->total() }}</span></h2></div></div>
        <div class="grid">
            @forelse($jobs as $job)<x-job-card :job="$job" />
            @empty<div class="empty-state panel"><x-icon name="briefcase" :size="36" /><h3>Por ahora no hay convocatorias abiertas</h3><p>Mientras tanto, puedes seguir explorando otros empleos de nuestra ciudad.</p><a href="{{ route('jobs.index') }}" class="button button-secondary">Explorar empleos <x-icon name="arrow" :size="18" /></a></div>@endforelse
        </div>
        @if($jobs->hasPages())<div class="pagination">{{ $jobs->links() }}</div>@endif
    </div>
    <aside class="stack">
        <div class="panel safety-card"><span class="category-icon"><x-icon name="users" :size="31" /></span><h3>Que conozcan tu talento</h3><p>Un perfil completo ayuda a las empresas a conocer tu experiencia, tus habilidades y tus ganas de crecer.</p><a class="button button-secondary button-full" href="{{ auth()->check() ? route('dashboard') : route('register') }}">{{ auth()->check() ? 'Ir a mi panel' : 'Crear mi perfil' }} <x-icon name="arrow" :size="18" /></a></div>
        <div class="panel safety-card"><x-icon name="shield" :size="29" /><h3>Una búsqueda más segura</h3><p>Revisa las condiciones del puesto antes de postular. Nunca compartas claves ni hagas pagos para participar.</p><a class="text-link" href="{{ route('resources.index') }}">Ver orientación laboral <x-icon name="arrow" :size="18" /></a></div>
    </aside>
</section>
@endsection
