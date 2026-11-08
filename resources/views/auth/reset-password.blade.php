@extends('layouts.app')
@section('title', 'Restablecer contraseña')
@section('content')
<section class="container auth-single">
    <div class="panel auth-card">
        <span class="auth-icon"><x-icon name="lock" :size="30" /></span><span class="eyebrow">UN NUEVO COMIENZO</span><h1>Elige tu nueva contraseña</h1><p>Utiliza una combinación que no uses en otros servicios.</p>
        <form method="POST" action="{{ route('password.update') }}" class="stack">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="field"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" value="{{ old('email', $email ?? request('email')) }}" autocomplete="email" required maxlength="255"></div>
            <div class="field"><label for="password">Nueva contraseña</label><div class="password-input"><input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required placeholder="Mínimo 8 caracteres"><button class="password-toggle" type="button" data-password-toggle aria-controls="password" aria-pressed="false" aria-label="Mostrar contraseña">Mostrar</button></div></div>
            <div class="field"><label for="password_confirmation">Confirma la contraseña</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required placeholder="Repite tu nueva contraseña"></div>
            <button type="submit" class="button button-full">Guardar nueva contraseña <x-icon name="arrow" :size="19" /></button>
        </form>
    </div>
</section>
@endsection
