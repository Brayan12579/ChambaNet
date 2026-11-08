@extends('layouts.app')
@section('title', 'Encuentra tu próximo empleo en Cusco')
@section('content')
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="hero-kicker"><span class="tiny-dot"></span> TU TALENTO TIENE UN LUGAR</span>
            <h1>Encuentra tu próximo<br><span>empleo en Cusco.</span></h1>
            <p>Oportunidades de nuestra ciudad, con la información que necesitas para dar el siguiente paso. Busca cerca de ti y postula con confianza.</p>
            <div class="hero-availability"><x-icon name="briefcase" :size="19" /><strong>{{ $jobCount }}</strong> {{ $jobCount === 1 ? 'oferta disponible' : 'ofertas disponibles' }} <span>·</span> negocios locales</div>
            <div class="hero-trust"><x-icon name="shield" :size="21" /><span>Postular es gratis.<br><strong>Tu CV se comparte cuando tú postulas.</strong></span></div>
        </div>
        <x-cusco-illustration />
    </div>
    <div class="container hero-search-wrap">
        <form class="hero-search" action="{{ route('jobs.index') }}" method="GET" role="search">
            <label class="search-field"><x-icon name="search" :size="23" /><span><span class="search-label">¿QUÉ TRABAJO BUSCAS?</span><input name="q" placeholder="Puesto o palabra clave" aria-label="Puesto o palabra clave" maxlength="120"></span></label>
            <label class="search-field search-location"><x-icon name="pin" :size="23" /><span><span class="search-label">DISTRITO</span><select name="district" aria-label="Distrito"><option value="">Todo Cusco</option>@foreach($districts as $district)<option value="{{ $district->id }}">{{ $district->name }}</option>@endforeach</select></span></label>
            <label class="search-field search-schedule"><x-icon name="clock" :size="23" /><span><span class="search-label">JORNADA</span><select name="schedule" aria-label="Jornada laboral"><option value="">Cualquier jornada</option>@foreach(\App\Models\JobOffer::SCHEDULES as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></span></label>
            <button class="button search-button" type="submit">Buscar empleo <x-icon name="arrow" :size="20" /></button>
        </form>
        <div class="search-hint"><div class="popular-searches"><span>Explora:</span><a href="{{ route('jobs.index', ['q' => 'cocina']) }}">Cocina</a><a href="{{ route('jobs.index', ['q' => 'atención']) }}">Atención al cliente</a><a href="{{ route('jobs.index', ['schedule' => 'part_time']) }}">Medio tiempo</a></div><span><x-icon name="pin" :size="17" /> Hecho para Cusco</span></div>
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
        <div class="demo-notice"><x-icon name="book" :size="19" /><span>Las ofertas etiquetadas <strong>Demo</strong> son ejemplos ficticios del proyecto. No representan vacantes reales.</span></div>
        <div class="grid-3 jobs-grid">
            @forelse($jobs as $job)<x-job-card :job="$job" />
            @empty<div class="empty-state panel"><x-icon name="briefcase" :size="38" /><h3>Las oportunidades están por llegar</h3><p>¿Tienes un negocio? Publica una vacante y conecta con el talento local.</p><a class="button" href="{{ auth()->check() ? route('dashboard') : route('register', ['role' => 'employer']) }}">Publicar mi primera oferta</a></div>@endforelse
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
        <div><span class="eyebrow">UN CAMINO CLARO HACIA TU PRÓXIMO EMPLEO</span><h2>Menos vueltas.<br>Más posibilidades.</h2><p>Organiza tu búsqueda, presenta tu talento y sigue cada postulación desde un solo lugar.</p><a class="button" href="{{ auth()->check() ? route('dashboard') : route('register') }}">{{ auth()->check() ? 'Continuar en mi panel' : 'Crear mi perfil gratis' }} <x-icon name="arrow" :size="19" /></a></div>
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
<section class="container employer-banner">
    <div><span class="eyebrow">EL TALENTO QUE BUSCAS ESTÁ CERCA</span><h2>Tu próximo gran equipo<br>empieza en tu ciudad.</h2><p>Publica tus vacantes con información clara y gestiona tus procesos de selección en un solo lugar.</p><a class="button button-lime" href="{{ auth()->check() ? route('dashboard') : route('register', ['role' => 'employer']) }}">Quiero encontrar talento <x-icon name="arrow" :size="19" /></a></div>
    <div class="banner-art" aria-hidden="true"><span class="banner-circle"></span><x-icon name="users" :size="120" /><span class="banner-star">✦</span><span class="banner-caption">Cusco tiene talento.</span></div>
</section>
@endsection
