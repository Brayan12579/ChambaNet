@extends('layouts.app')
@section('title', 'Empresas y negocios de Cusco')
@section('content')
<section class="page-hero"><div class="container directory-heading"><div><span class="eyebrow">NEGOCIOS LOCALES. NUEVAS POSIBILIDADES.</span><h1>Conoce a tu<br>próximo equipo<span class="heading-dot">.</span></h1><p>Descubre a los negocios de nuestra comunidad y las oportunidades que tienen para ti.</p></div><img class="directory-photo" src="{{ asset('images/equipo-chambanet.webp') }}" srcset="{{ asset('images/equipo-chambanet-640.webp') }} 640w, {{ asset('images/equipo-chambanet.webp') }} 1200w" sizes="(max-width: 619px) calc(100vw - 36px), (max-width: 899px) 45vw, 560px" width="1200" height="800" alt="Un equipo de profesionales comparte ideas en su lugar de trabajo" decoding="async"></div></section>
<section class="container section">
    <div class="section-heading"><div><h2>Empresas de nuestra comunidad</h2><p>{{ $companies->total() }} {{ $companies->total() === 1 ? 'empresa registrada' : 'empresas registradas' }} en Cusco.</p></div><a class="text-link" href="{{ route('jobs.index', ['verified' => 1]) }}"><x-icon name="shield" :size="19" />Ofertas de empresas verificadas <x-icon name="arrow" :size="18" /></a></div>
    @if($companies->contains('is_demo', true))<div class="demo-notice"><x-icon name="book" :size="19" /><span>Los perfiles etiquetados <strong>Demo</strong> pertenecen a empresas ficticias de demostración.</span></div>@endif
    <div class="companies-grid">
        @forelse($companies as $company)<x-company-card :company="$company" />
        @empty<div class="empty-state panel"><span class="category-icon"><x-icon name="building" :size="36" /></span><h2>La comunidad está comenzando</h2><p>¿Tienes un negocio en Cusco? Presenta tu empresa y conecta con personas que quieren sumar a tu equipo.</p><a href="{{ auth()->check() ? route('dashboard') : route('register', ['role' => 'employer']) }}" class="button">Registrar mi empresa <x-icon name="arrow" :size="18" /></a></div>@endforelse
    </div>
    @if($companies->hasPages())<div class="pagination">{{ $companies->links() }}</div>@endif
</section>
@endsection
