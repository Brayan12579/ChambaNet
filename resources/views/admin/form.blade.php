@extends('layouts.app')
@section('title', ($record->exists ? 'Editar ' : 'Crear ').mb_strtolower($title))
@section('content')
<div class="container section dashboard-layout">
    @include('admin.sidebar')
    <div class="dashboard-main stack">
        <header class="page-heading"><div><a class="back-link" href="{{ route('admin.content.index', ['section' => $section]) }}">← Volver a {{ mb_strtolower($title) }}</a><span class="eyebrow">GESTIÓN DE LA PLATAFORMA</span><h1>{{ $section === 'reports' ? 'Revisar reporte' : ($record->exists ? 'Editar registro' : 'Crear registro') }}</h1><p>{{ $title }} · Los cambios se reflejarán en la plataforma.</p></div></header>
        @if($section === 'reports')
            @php $reason = match($record->reason) { 'fraud' => 'Posible fraude', 'inappropriate' => 'Contenido inapropiado', 'misleading' => 'Información engañosa', 'other' => 'Otro motivo', default => $record->reason }; @endphp
            <section class="panel stack" aria-labelledby="reported-offer">
                <div class="actions"><span class="eyebrow">INFORMACIÓN DEL REPORTE</span><span class="badge {{ $record->status === 'pending' ? 'badge-danger' : 'badge-success' }}">{{ \App\Models\Report::STATUSES[$record->status] ?? $record->status }}</span></div><h2 id="reported-offer">{{ $reason }}</h2>
                <dl class="report-detail"><div><dt>Oferta reportada</dt><dd>{{ $record->jobOffer->title }}</dd></div><div><dt>Enviado por</dt><dd>{{ $record->user->name }}</dd></div><div><dt>Fecha</dt><dd>{{ $record->created_at->format('d/m/Y H:i') }}</dd></div><div><dt>Detalle del reporte</dt><dd class="prose-content">{{ $record->details }}</dd></div></dl>
                <div class="actions"><a class="button button-secondary" href="{{ route('admin.content.edit', ['section' => 'jobs', 'record' => $record->job_offer_id]) }}">Moderar oferta <x-icon name="arrow" :size="18" /></a>@if($record->jobOffer->status !== 'closed')<form method="POST" action="{{ route('admin.jobs.close', $record->jobOffer) }}" data-confirm="¿Cerrar esta oferta tras revisar el reporte? Dejará de recibir postulaciones y conservará el historial.">@csrf @method('PATCH')<button class="button button-danger" type="submit">Cerrar publicación</button></form>@else<span class="badge badge-neutral">Publicación cerrada</span>@endif</div>
                <p>Revisa la publicación y sus condiciones. Después registra aquí el resultado de la revisión.</p>
            </section>
        @endif
        @if($section === 'companies' && $record->exists)
            @php
                $verificationFields = ['name' => 'Nombre comercial', 'description' => 'Descripción del negocio', 'sector' => 'Rubro', 'address' => 'Dirección', 'district_id' => 'Distrito'];
                $missingVerificationFields = collect($verificationFields)->filter(fn ($label, $field) => !filled($record->getAttribute($field)));
            @endphp
            <section class="panel stack">
                <div class="page-heading"><div><span class="eyebrow">VERIFICACIÓN EMPRESARIAL</span><h2>Una revisión que genera confianza</h2></div><span class="badge {{ $record->verified ? 'badge-success' : 'badge-warning' }}">{{ $record->verified ? 'Empresa verificada' : 'Pendiente de verificación' }}</span></div>
                @if($record->is_demo)<div class="demo-notice"><x-icon name="book" :size="19" /><span>Este negocio es ficticio. Cualquier verificación se utiliza únicamente como demostración.</span></div>@endif
                <p>Revisa la identidad de la empresa y contrasta la información del perfil antes de aprobarla.</p>
                <ul class="verification-checklist">@foreach($verificationFields as $field => $label)<li><x-icon :name="filled($record->getAttribute($field)) ? 'check' : 'close'" :size="18" />{{ $label }} <span class="muted">· {{ filled($record->getAttribute($field)) ? 'Completado' : 'Falta completar' }}</span></li>@endforeach</ul>
                @if($missingVerificationFields->isNotEmpty())<div class="alert alert-warning"><span>Completa los campos pendientes y guarda los cambios antes de verificar.</span></div>
                @elseif(!$record->verified)<form action="{{ route('admin.companies.verify', $record) }}" method="POST" data-confirm="¿Verificar a {{ $record->name }}? Confirma que has revisado sus datos y la identidad del negocio.">@csrf @method('PATCH')<button class="button" type="submit"><x-icon name="shield" :size="19" />Verificar empresa</button></form>@endif
            </section>
        @endif
        <form class="panel stack" method="POST" action="{{ $record->exists ? route('admin.content.update', ['section' => $section, 'record' => $record->id]) : route('admin.content.store', ['section' => $section]) }}">
            @csrf
            @if($record->exists) @method('PUT') @endif
            @if($section === 'reports')<h2>Resultado de la revisión</h2>@endif
            <div class="form-grid">
                @foreach($fields as $name => $configuration)
                    @php
                        [$label, $type] = $configuration;
                        $default = $record->getAttribute($name);
                        if (!$record->exists && $type === 'checkbox' && $name === 'active') { $default = true; }
                        $optional = $name === 'website' || ($type === 'password' && $record->exists);
                        $maximum = match ($name) { 'email', 'website', 'address' => 255, 'sector' => 120, 'name' => in_array($section, ['categories', 'districts']) ? 120 : 160, default => 180 };
                    @endphp
                    @if($type === 'checkbox')
                        <div class="field"><input type="hidden" name="{{ $name }}" value="0"><label class="checkbox-label" for="admin-{{ $name }}"><input id="admin-{{ $name }}" type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $default))>{{ $label }}</label>@error($name)<span class="field-error">{{ $message }}</span>@enderror</div>
                    @else
                        <div class="field {{ $type === 'textarea' ? 'field-full' : '' }}">
                            <label for="admin-{{ $name }}">{{ $label }}@unless($optional) <span aria-hidden="true">*</span>@endunless</label>
                            @if($type === 'select')
                                <select id="admin-{{ $name }}" name="{{ $name }}" required aria-describedby="admin-{{ $name }}-error"><option value="">Selecciona una opción</option>@foreach($configuration[2] as $value => $option)<option value="{{ $value }}" @selected((string) old($name, $default) === (string) $value)>{{ $option }}</option>@endforeach</select>
                            @elseif($type === 'textarea')
                                <textarea id="admin-{{ $name }}" name="{{ $name }}" rows="{{ $name === 'body' ? 12 : 5 }}" required maxlength="{{ $name === 'body' ? 30000 : ($name === 'excerpt' ? 500 : 5000) }}" aria-describedby="admin-{{ $name }}-error">{{ old($name, $default) }}</textarea>
                            @elseif($type === 'password')
                                <input id="admin-{{ $name }}" type="password" name="{{ $name }}" minlength="8" autocomplete="new-password" @required(!$optional) aria-describedby="admin-{{ $name }}-error">
                            @else
                                <input id="admin-{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $default) }}" @required(!$optional) maxlength="{{ $maximum }}" aria-describedby="admin-{{ $name }}-error">
                            @endif
                            <span id="admin-{{ $name }}-error" class="field-error">@error($name){{ $message }}@enderror</span>
                        </div>
                    @endif
                @endforeach
            </div>
            @if($section === 'jobs') @include('employer.job-fields', ['job' => $record]) @endif
            @if($section === 'users' && !$record->exists)<p>El nuevo usuario recibirá un perfil según el rol seleccionado. Comparte sus credenciales por un canal privado.</p>@endif
            <div class="actions"><button class="button" type="submit">{{ $record->exists ? 'Guardar cambios' : 'Crear registro' }} <x-icon name="check" :size="19" /></button><a class="button button-ghost" href="{{ route('admin.content.index', ['section' => $section]) }}">Cancelar</a></div>
        </form>
    </div>
</div>
@endsection
