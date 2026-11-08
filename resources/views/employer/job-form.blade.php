@extends('layouts.app')
@section('title', $job->exists ? 'Editar oferta' : 'Publicar oferta')
@section('content')
<section class="section container">
    <div class="page-heading"><div><p class="eyebrow">UNA OPORTUNIDAD CON INFORMACIÓN CLARA</p><h1>{{ $job->exists ? 'Edita tu oferta' : 'Encuentra a tu próximo equipo' }}</h1><p class="muted">Los campos con * son obligatorios. Describe requisitos relacionados con el puesto y condiciones concretas.</p></div></div>
    <div class="dashboard-layout">@include('employer.sidebar')<div class="dashboard-main panel">
        <form action="{{ $job->exists ? route('employer.jobs.update', $job) : route('employer.jobs.store') }}" method="POST" class="stack">@csrf @if($job->exists) @method('PUT') @endif
            @include('employer.job-fields')
            <div class="actions"><button class="button" type="submit">{{ $job->exists ? 'Guardar cambios' : 'Crear oferta' }}</button><a class="button-secondary" href="{{ route('employer.jobs.index') }}">Volver a mis ofertas</a></div>
        </form>
    </div></div>
</section>
@endsection