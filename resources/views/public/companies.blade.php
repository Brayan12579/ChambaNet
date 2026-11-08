@extends('layouts.app')
@section('title', 'Empresas y negocios de Cusco')
@section('content')
<section class="page-hero"><div class="container"><span class="eyebrow">NEGOCIOS LOCALES. NUEVAS POSIBILIDADES.</span><h1>Tu próximo equipo<br>está más cerca<span class="heading-dot">.</span></h1><p>Conoce a los negocios que forman parte de ChambaNet y descubre sus oportunidades.</p></div></section>
<section class="container section">
    <div class="section-heading"><div><h2>Empresas de nuestra comunidad</h2><p>{{ $companies->total() }} {{ $companies->total() === 1 ? 'empresa registrada' : 'empresas registradas' }} en Cusco.</p></div><a class="text-link" href="{{ route('jobs.index', ['verified' => 1]) }}"><x-icon name="shield" :size="19" />Ofertas de empresas verificadas <x-icon name="arrow" :size="18" /></a></div>
    <div class="demo-notice"><x-icon name="book" :size="19" /><span>Los perfiles etiquetados <strong>Demo</strong> pertenecen a empresas ficticias de demostración.</span></div>
    <div class="companies-grid">
        @forelse($companies as $company)<x-company-card :company="$company" />
        @empty<div class="empty-state panel"><span class="category-icon"><x-icon name="building" :size="36" /></span><h2>La comunidad está comenzando</h2><p>¿Tienes un negocio en Cusco? Presenta tu empresa y conecta con personas que quieren sumar a tu equipo.</p><a href="{{ auth()->check() ? route('dashboard') : route('register', ['role' => 'employer']) }}" class="button">Registrar mi empresa <x-icon name="arrow" :size="18" /></a></div>@endforelse
    </div>
    @if($companies->hasPages())<div class="pagination">{{ $companies->links() }}</div>@endif
</section>
@endsection
