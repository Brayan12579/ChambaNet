@extends('layouts.app')
@section('title', 'Recuperar contraseña')
@section('content')
<section class="container auth-single">
    <div class="panel auth-card">
        <span class="auth-icon"><x-icon name="mail" :size="30" /></span><span class="eyebrow">RECUPERA EL ACCESO</span><h1>Volvamos a tu camino</h1><p>Introduce el correo de tu cuenta para solicitar un enlace de recuperación.</p>
        @if(config('mail.default') === 'log')<div class="alert alert-warning"><span>Este entorno registra los correos de desarrollo en el servidor. La entrega a tu bandeja estará disponible al configurar un proveedor SMTP.</span></div>@endif
        <form method="POST" action="{{ route('password.email') }}" class="stack">
            @csrf
            <div class="field"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" placeholder="tucorreo@ejemplo.com" required autofocus maxlength="255">@error('email')<span class="field-error">{{ $message }}</span>@enderror</div>
            <button type="submit" class="button button-full">Solicitar enlace <x-icon name="arrow" :size="19" /></button>
        </form>
        <a class="back-link" href="{{ route('login') }}">← Volver a iniciar sesión</a>
    </div>
</section>
@endsection
