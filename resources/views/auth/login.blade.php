@extends('layouts.app')
@section('title', 'Iniciar sesión')
@section('content')
<section class="auth-section container">
    <div class="auth-story">
        <span class="eyebrow">TU HISTORIA CONTINÚA AQUÍ</span><h1>Un nuevo paso.<br>Más cerca de<br>lo que buscas<span class="heading-dot">.</span></h1>
        <p>Accede a tus oportunidades, postulaciones y próximos pasos desde un solo lugar.</p>
        <div class="auth-story-art"><x-icon name="briefcase" :size="70" /><span>Tu talento tiene un lugar en Cusco.</span><i aria-hidden="true">✦</i></div>
        <div class="auth-security"><x-icon name="shield" :size="25" /><span>Tu CV es privado.<br><strong>Solo lo recibe la empresa a la que postulas.</strong></span></div>
    </div>
    <div class="auth-card panel">
        <span class="eyebrow">BIENVENIDO DE VUELTA</span><h2>Inicia sesión</h2><p>Qué bueno tenerte otra vez en ChambaNet.</p>
        <form action="{{ route('login.store') }}" method="POST" class="stack">
            @csrf
            <div class="field"><label for="email">Correo electrónico</label><input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="tucorreo@ejemplo.com" autocomplete="email" required autofocus maxlength="255" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>@error('email')<span class="field-error" id="email-error">{{ $message }}</span>@enderror</div>
            <div class="field"><label for="password">Contraseña</label><div class="password-input"><input type="password" id="password" name="password" autocomplete="current-password" placeholder="Tu contraseña" required><button class="password-toggle" type="button" data-password-toggle aria-controls="password" aria-pressed="false" aria-label="Mostrar contraseña">Mostrar</button></div></div>
            <div class="auth-options"><label class="checkbox-label"><input type="checkbox" name="remember" value="1" @checked(old('remember'))>Recordarme</label><a class="text-link" href="{{ route('password.request') }}">Olvidé mi contraseña</a></div>
            <button class="button button-full" type="submit">Entrar a mi cuenta <x-icon name="arrow" :size="19" /></button>
        </form>
        <div class="auth-divider"></div><p class="auth-switch">¿Es tu primera vez aquí?<br><a class="text-link" href="{{ route('register') }}">Crea tu cuenta gratis <x-icon name="arrow" :size="17" /></a></p>
    </div>
</section>
@endsection
