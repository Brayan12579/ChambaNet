@extends('layouts.app')
@section('title', 'Crea tu cuenta')
@section('content')
<section class="auth-section container">
    <div class="auth-story">
        <span class="eyebrow">TU PRÓXIMO CAPÍTULO EMPIEZA AQUÍ</span><h1>Más oportunidades.<br>Un futuro<br>más tuyo<span class="heading-dot">.</span></h1>
        <p>Encuentra un empleo que vaya contigo o conecta tu negocio con el talento de Cusco.</p>
        <div class="auth-benefits"><div><x-icon name="check" :size="22" /><span>Empleos de negocios locales, cerca de ti.</span></div><div><x-icon name="check" :size="22" /><span>Tus postulaciones organizadas en un solo lugar.</span></div><div><x-icon name="check" :size="22" /><span>Tu información y tu CV bajo tu control.</span></div></div>
        <div class="auth-story-art compact"><x-icon name="users" :size="65" /><span>Crezcamos juntos.</span><i aria-hidden="true">✦</i></div>
    </div>
    <div class="auth-card panel">
        <span class="eyebrow">EMPECEMOS CON LO ESENCIAL</span><h2>Crea tu cuenta gratis</h2><p>Elige cómo quieres formar parte de ChambaNet.</p>
        <form action="{{ route('register.store') }}" method="POST" class="stack" data-register-form>
            @csrf
            <fieldset class="role-picker">
                <legend>¿Qué estás buscando?</legend>
                <label><input type="radio" name="role" value="candidate" @checked(old('role', request('role', 'candidate')) !== 'employer')><span><x-icon name="users" :size="24" /><strong>Busco empleo</strong><small>Crear mi perfil</small></span></label>
                <label><input type="radio" name="role" value="employer" @checked(old('role', request('role')) === 'employer')><span><x-icon name="building" :size="24" /><strong>Busco talento</strong><small>Publicar ofertas</small></span></label>
            </fieldset>
            <div class="field"><label for="name">Nombres y apellidos</label><input id="name" name="name" value="{{ old('name') }}" placeholder="¿Cómo te llamas?" autocomplete="name" required maxlength="120" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>@error('name')<span class="field-error" id="name-error">{{ $message }}</span>@enderror</div>
            <div class="field" data-company-field @if(old('role', request('role')) !== 'employer') hidden @endif><label for="company_name">Nombre de tu empresa o negocio</label><input id="company_name" name="company_name" value="{{ old('company_name') }}" placeholder="Nombre comercial" autocomplete="organization" maxlength="160" @required(old('role', request('role')) === 'employer')>@error('company_name')<span class="field-error">{{ $message }}</span>@enderror</div>
            <div class="field"><label for="email">Correo electrónico</label><input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="tucorreo@ejemplo.com" autocomplete="email" required maxlength="190" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>@error('email')<span class="field-error" id="email-error">{{ $message }}</span>@enderror</div>
            <div class="field"><label for="password">Crea una contraseña</label><div class="password-input"><input type="password" id="password" name="password" autocomplete="new-password" required minlength="8" placeholder="Mínimo 8 caracteres" aria-describedby="password-help"><button class="password-toggle" type="button" data-password-toggle aria-controls="password" aria-pressed="false" aria-label="Mostrar contraseña">Mostrar</button></div><small id="password-help">Usa una combinación que puedas recordar y no utilices en otros servicios.</small></div>
            <div class="field"><label for="password_confirmation">Confirma tu contraseña</label><input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required minlength="8" placeholder="Repite tu contraseña"></div>
            <p class="form-note"><x-icon name="lock" :size="18" /><span>Usamos tus datos para gestionar tu cuenta. Tu CV solo se comparte con las empresas a las que postulas.</span></p>
            <button class="button button-full" type="submit">Crear mi cuenta <x-icon name="arrow" :size="19" /></button>
        </form>
        <p class="auth-switch">¿Ya formas parte de ChambaNet? <a class="text-link" href="{{ route('login') }}">Inicia sesión</a></p>
    </div>
</section>
@endsection
