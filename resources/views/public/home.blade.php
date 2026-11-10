@extends('layouts.app')
@section('title', 'Encuentra tu próximo empleo en Cusco')
@section('content')
<section class="hero" aria-labelledby="home-title">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="hero-kicker"><span class="tiny-dot"></span> EMPLEO LOCAL · CUSCO, PERÚ</span>
            <h1 id="home-title">Tu talento.<br>Tu ciudad.<br><span>Tu próximo paso.</span></h1>
            <p>Conectamos personas con oportunidades de trabajo en Cusco. Encuentra tu lugar o a quienes harán crecer tu equipo.</p>
            <div class="hero-entry-actions">
                @guest
                    <a class="button button-lime" href="{{ route('jobs.index') }}">Quiero encontrar empleo <x-icon name="arrow" :size="18" /></a>
                    <a class="button button-hero-secondary" href="{{ route('register', ['role' => 'employer']) }}">Quiero contratar</a>
                @else
                    <a class="button button-lime" href="{{ route('dashboard') }}">{{ match(auth()->user()->role) { 'admin' => 'Abrir administración', 'employer' => 'Gestionar mi empresa', default => 'Continuar mi búsqueda' } }} <x-icon name="arrow" :size="18" /></a>
                @endguest
            </div>
            <div class="hero-trust"><x-icon name="shield" :size="19" /><span>Postula gratis. Comparte tu CV solo con quien eliges.</span></div>
        </div>
        <figure class="hero-photo">
            <img src="{{ asset('images/equipo-chambanet.webp') }}" srcset="{{ asset('images/equipo-chambanet-640.webp') }} 640w, {{ asset('images/equipo-chambanet.webp') }} 1200w" sizes="(max-width: 619px) calc(100vw - 36px), (max-width: 899px) 45vw, 560px" width="1200" height="800" alt="Profesionales colaborando alrededor de una computadora en un espacio de trabajo luminoso" fetchpriority="high" decoding="async">
            <figcaption><span>EL TALENTO ESTÁ CERCA</span><strong>Personas que suman.<br>Equipos que crecen.</strong><span class="photo-location"><x-icon name="pin" :size="16" />Inspirado en nuestra comunidad</span></figcaption>
        </figure>
    </div>
    <div class="container hero-search-wrap">
        <form class="hero-search" action="{{ route('jobs.index') }}" method="GET" role="search">
            <label class="search-field"><x-icon name="search" :size="23" /><span><span class="search-label">¿QUÉ TRABAJO BUSCAS?</span><input name="q" placeholder="Puesto o palabra clave" aria-label="Puesto o palabra clave" maxlength="120"></span></label>
            <label class="search-field search-location"><x-icon name="pin" :size="23" /><span><span class="search-label">DISTRITO</span><select name="district" aria-label="Distrito"><option value="">Todo Cusco</option>@foreach($districts as $district)<option value="{{ $district->id }}">{{ $district->name }}</option>@endforeach</select></span></label>
            <label class="search-field search-schedule"><x-icon name="clock" :size="23" /><span><span class="search-label">JORNADA</span><select name="schedule" aria-label="Jornada laboral"><option value="">Cualquier jornada</option>@foreach(\App\Models\JobOffer::SCHEDULES as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></span></label>
            <button class="button search-button" type="submit">Buscar empleo <x-icon name="arrow" :size="20" /></button>
        </form>
        <div class="search-hint"><div class="popular-searches"><span>Búsquedas rápidas:</span><a href="{{ route('jobs.index', ['q' => 'cocina']) }}">Cocina</a><a href="{{ route('jobs.index', ['q' => 'atención']) }}">Atención al cliente</a><a href="{{ route('jobs.index', ['schedule' => 'part_time']) }}">Medio tiempo</a></div><span><x-icon name="briefcase" :size="17" /><strong>{{ $jobCount }}</strong> {{ $jobCount === 1 ? 'oferta disponible' : 'ofertas disponibles' }}</span></div>
    </div>
</section>
<section class="section categories-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">UN LUGAR PARA CADA TALENTO</span><h2>¿En qué te gustaría trabajar?</h2><p>Empieza por lo que sabes hacer o descubre algo nuevo.</p></div><a class="text-link" href="{{ route('jobs.index') }}">Todas las categorías <x-icon name="arrow" :size="19" /></a></div>
        <div class="categories-grid">
            @forelse($categories as $category)
                <a class="category-card" href="{{ route('jobs.index', ['category' => $category->id]) }}"><span class="category-icon"><x-icon :name="['coffee','cart','building','truck','users','briefcase'][$loop->index % 6]" :size="27" /></span><h3>{{ $category->name }}</h3><span>{{ $category->job_offers_count }} {{ $category->job_offers_count === 1 ? 'oportunidad' : 'oportunidades' }} <x-icon name="arrow" :size="17" /></span></a>
            @empty
                <div class="empty-state panel">Las categorías aparecerán aquí cuando estén disponibles.</div>
            @endforelse
        </div>
    </div>
</section>
<section class="section recent-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">EL MOMENTO DE COMENZAR ES AHORA</span><h2>Oportunidades recién llegadas<span class="heading-dot">.</span></h2><p>Conoce los últimos puestos publicados en nuestra ciudad.</p></div><a class="button button-secondary" href="{{ route('jobs.index') }}">Ver todos los empleos <x-icon name="arrow" :size="19" /></a></div>
        @if($jobs->contains('is_demo', true))<div class="demo-notice"><x-icon name="book" :size="19" /><span>Las ofertas etiquetadas <strong>Demo</strong> son ejemplos ficticios del proyecto. No representan vacantes reales.</span></div>@endif
        <div class="grid-3 jobs-grid">
            @forelse($jobs as $job)<x-job-card :job="$job" />
            @empty<div class="empty-state panel"><x-icon name="briefcase" :size="38" /><h3>Las oportunidades están por llegar</h3><p>{{ auth()->user()?->role === 'candidate' ? 'Completa tu perfil y prepara tu CV para las próximas publicaciones.' : '¿Tienes un negocio? Publica una vacante y conecta con el talento local.' }}</p><a class="button" href="{{ auth()->check() ? route('dashboard') : route('register', ['role' => 'employer']) }}">{{ auth()->user()?->role === 'candidate' ? 'Preparar mi perfil' : (auth()->check() ? 'Ir a mi panel' : 'Publicar mi primera oferta') }}</a></div>@endforelse
        </div>
    </div>
</section>
@if($featured->isNotEmpty())
    <section class="section featured-section">
        <div class="container">
            <div class="section-heading"><div><span class="eyebrow">OPORTUNIDADES QUE MERECEN UNA MIRADA</span><h2>Da el siguiente paso</h2><p>Explora las publicaciones destacadas de la plataforma.</p></div><span class="section-note"><x-icon name="spark" :size="20" />Ofertas destacadas</span></div>
            <div class="grid-3">@foreach($featured as $job)<x-job-card :job="$job" />@endforeach</div>
            <a class="text-link verified-link" href="{{ route('jobs.index', ['verified' => 1]) }}"><x-icon name="shield" :size="19" />Explorar empresas verificadas <x-icon name="arrow" :size="18" /></a>
        </div>
    </section>
@endif
<section class="section companies-section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">NEGOCIOS DE NUESTRA COMUNIDAD</span><h2>Conoce a tu próximo equipo</h2><p>Personas y negocios que hacen crecer Cusco.</p></div><a class="text-link" href="{{ route('companies.index') }}">Conocer empresas <x-icon name="arrow" :size="19" /></a></div>
        <div class="companies-grid">@forelse($companies as $company)<x-company-card :company="$company" />@empty<div class="empty-state panel">Sé la primera empresa en formar parte de ChambaNet.</div>@endforelse</div>
    </div>
</section>
<section class="section how-section">
    <div class="container how-grid">
        <div><span class="eyebrow">ASÍ FUNCIONA CHAMBANET</span><h2>De la búsqueda<br>al siguiente paso.</h2><p>Un proceso sencillo para encontrar oportunidades y llevar el control de tus postulaciones.</p><a class="button" href="{{ auth()->check() ? route('dashboard') : route('register') }}">{{ auth()->check() ? 'Continuar en mi panel' : 'Crear mi perfil gratis' }} <x-icon name="arrow" :size="19" /></a><div class="benefit-note"><x-icon name="lock" :size="24" /><div><strong>Tu información, bajo tu control.</strong><p>Tu CV se comparte cuando envías una postulación, con esa empresa.</p></div></div></div>
        <div class="steps">
            <div class="step"><span>01</span><div><h3>Cuenta tu historia</h3><p>Completa tu perfil y agrega tu CV. Tu experiencia y tus ganas de aprender cuentan.</p></div></div>
            <div class="step"><span>02</span><div><h3>Encuentra lo que va contigo</h3><p>Filtra por distrito, jornada, categoría y salario. Guarda las ofertas que te interesen.</p></div></div>
            <div class="step"><span>03</span><div><h3>Postula y sigue tu avance</h3><p>Envía tu candidatura y consulta las respuestas de cada empresa en tu cuenta.</p></div></div>
        </div>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="section-heading"><div><span class="eyebrow">PREPÁRATE PARA LO QUE VIENE</span><h2>Herramientas para dar el salto</h2><p>Consejos prácticos para presentar tu talento con confianza.</p></div><a class="text-link" href="{{ route('resources.index') }}">Todos los recursos <x-icon name="arrow" :size="19" /></a></div>
        <div class="grid-3">@forelse($resources as $resource)<x-resource-card :resource="$resource" :index="$loop->index" />@empty<div class="empty-state panel">Pronto encontrarás guías de orientación laboral aquí.</div>@endforelse</div>
    </div>
</section>
@if(!auth()->check() || auth()->user()->role === 'employer')
<section class="container employer-banner">
    <div><span class="eyebrow">EL TALENTO QUE BUSCAS ESTÁ CERCA</span><h2>Tu próximo gran equipo<br>empieza en tu ciudad.</h2><p>Publica tus vacantes con información clara y gestiona tus procesos de selección en un solo lugar.</p><a class="button button-lime" href="{{ auth()->check() ? route('employer.jobs.create') : route('register', ['role' => 'employer']) }}">Quiero encontrar talento <x-icon name="arrow" :size="19" /></a></div>
    <div class="banner-art" aria-hidden="true"><span class="banner-circle"></span><x-icon name="users" :size="120" /><span class="banner-star">✦</span><span class="banner-caption">Cusco tiene talento.</span></div>
</section>
@endif
@endsection
