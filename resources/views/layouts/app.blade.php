<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#183f35">
    <meta name="description" content="Encuentra empleo en Cusco con ChambaNet. Explora oportunidades de negocios locales, guarda ofertas y sigue tus postulaciones en un solo lugar.">
    <title>@yield('title', 'Tu próximo empleo en Cusco') · ChambaNet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#main">Saltar al contenido</a>
<header class="site-header">
    <div class="container navbar">
        <a class="brand" href="{{ route('home') }}" aria-label="ChambaNet, inicio">
            <span class="brand-mark"><x-icon name="briefcase" :size="23" /></span>
            <span>Chamba<span class="brand-accent">Net<span class="brand-dot">.</span></span></span>
        </a>
        <button class="menu-toggle" type="button" data-menu-toggle aria-controls="main-nav" aria-expanded="false" aria-label="Abrir menú de navegación">
            <x-icon name="menu" :size="25" />
        </button>
        <nav id="main-nav" class="main-nav" aria-label="Navegación principal">
            <a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')]) @if(request()->routeIs('home')) aria-current="page" @endif>Inicio</a>
            <a href="{{ route('jobs.index') }}" @class(['active' => request()->routeIs('jobs.*')]) @if(request()->routeIs('jobs.*')) aria-current="page" @endif>Empleos</a>
            <a href="{{ route('companies.index') }}" @class(['active' => request()->routeIs('companies.*')]) @if(request()->routeIs('companies.*')) aria-current="page" @endif>Empresas</a>
            <a href="{{ route('resources.index') }}" @class(['active' => request()->routeIs('resources.*')]) @if(request()->routeIs('resources.*')) aria-current="page" @endif>Recursos</a>
            <div class="nav-actions">
                @auth
                    <a class="notification-bell" href="{{ route('notifications.index') }}" aria-label="Notificaciones. {{ $unreadNotificationCount }} sin leer"><x-icon name="bell" :size="22" />@if($unreadNotificationCount > 0)<span class="notification-count" aria-hidden="true">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>@endif<span class="sr-only">Notificaciones</span></a>
                    <a class="nav-login nav-account" href="{{ route('dashboard') }}">
                        <span class="account-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span>Mi panel</span><x-icon name="arrow" :size="16" />
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="button button-secondary button-sm" type="submit">Salir</button>
                    </form>
                @else
                    <a class="nav-login" href="{{ route('login') }}">Iniciar sesión</a>
                    <a class="button button-sm" href="{{ route('register', ['role' => 'employer']) }}">Publicar oferta <x-icon name="arrow" :size="17" /></a>
                @endauth
            </div>
        </nav>
    </div>
</header>
@if(session('status'))
    <div class="container flash-wrap"><div class="alert alert-success" role="status"><x-icon name="check" /><span>{{ session('status') }}</span></div></div>
@endif
@if(session('success'))
    <div class="container flash-wrap"><div class="alert alert-success" role="status"><x-icon name="check" /><span>{{ session('success') }}</span></div></div>
@endif
@if(session('error'))
    <div class="container flash-wrap"><div class="alert alert-error" role="alert"><span>{{ session('error') }}</span></div></div>
@endif
@if($errors->any())
    <div class="container flash-wrap">
        <div class="alert alert-error" role="alert" tabindex="-1" data-validation-summary>
            <div><strong>Hay algunos datos que revisar.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        </div>
    </div>
@endif
<main id="main" tabindex="-1">@yield('content')</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark"><x-icon name="briefcase" :size="23" /></span><span>Chamba<span class="brand-accent">Net<span class="brand-dot">.</span></span></span></a>
            <p>Oportunidades cercanas.<br>Un futuro con más posibilidades.</p>
            <span class="footer-location"><x-icon name="pin" :size="17" /> Cusco, Perú</span>
        </div>
        <div><h3>Para candidatos</h3><a href="{{ route('jobs.index') }}">Explorar empleos</a><a href="{{ route('companies.index') }}">Conocer empresas</a><a href="{{ route('register') }}">Crear mi perfil</a><a href="{{ route('resources.index') }}">Prepararme para postular</a></div>
        <div><h3>Para empresas</h3><a href="{{ auth()->check() ? route('dashboard') : route('register', ['role' => 'employer']) }}">Publicar una oferta</a><a href="{{ route('login') }}">Acceder a mi cuenta</a><a href="{{ route('password.request') }}">Recuperar mi acceso</a></div>
        <div class="footer-note"><span class="eyebrow">EL TALENTO ESTÁ CERCA</span><p>Conectamos personas y negocios locales con información clara y procesos de selección organizados.</p><span class="badge badge-demo">Las ofertas “Demo” son ficticias</span></div>
    </div>
    <div class="container footer-bottom"><span>© {{ date('Y') }} ChambaNet. Hecho para nuestra comunidad.</span><span>Tu talento. Tu ciudad. Tu próximo paso. <span aria-hidden="true">✦</span></span></div>
</footer>
@stack('scripts')
</body>
</html>
