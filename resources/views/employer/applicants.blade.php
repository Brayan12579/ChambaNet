@extends('layouts.app')
@section('title', 'Postulaciones recibidas')
@section('content')
<section class="section container">
    <div class="page-heading"><div><p class="eyebrow">TU PRÓXIMA CONTRATACIÓN</p><h1>{{ $job->title }}</h1><p class="muted">{{ $applications->total() }} postulaciones recibidas</p></div><a class="button-secondary" href="{{ route('employer.jobs.index') }}">Volver a mis ofertas</a></div>
    <div class="dashboard-layout">@include('employer.sidebar')<div class="dashboard-main stack">
        @forelse($applications as $application)<article class="panel">
            <div class="page-heading"><div><x-application-status :status="$application->status" /><h2>{{ $application->user->name }}</h2><p class="muted">Postuló el {{ $application->created_at->format('d/m/Y H:i') }}</p></div>@if($application->cv_path)<a class="button-secondary" href="{{ route('applications.cv', $application) }}" target="_blank" rel="noopener">Revisar CV en PDF ↗</a>@endif</div>
            @if($application->user->candidateProfile?->bio)<p>{{ $application->user->candidateProfile->bio }}</p>@endif
            @if($application->cover_letter)<h3>Mensaje del candidato</h3><p style="white-space:pre-line">{{ $application->cover_letter }}</p>@endif
            @if($application->status !== 'withdrawn')
                <p class="muted">{{ $application->user->email }} @if($application->user->candidateProfile?->phone) · {{ $application->user->candidateProfile->phone }} @endif</p>
                <form action="{{ route('employer.applications.update', $application) }}" method="POST" class="actions">@csrf @method('PATCH')<label for="application-{{ $application->id }}">Actualizar estado</label><select id="application-{{ $application->id }}" name="status">@foreach(\App\Models\Application::STATUSES as $value => $label)@if($value !== 'withdrawn')<option value="{{ $value }}" @selected($application->status === $value)>{{ $label }}</option>@endif @endforeach</select><button class="button" type="submit">Guardar estado</button></form>
            @else<p class="muted">El candidato retiró esta postulación. Se conserva el historial.</p>@endif
        </article>@empty<div class="empty-state panel"><h2>Aún no hay postulaciones</h2><p>Cuando alguien postule a esta oferta, podrás revisar su CV y actualizar su estado desde aquí.</p></div>@endforelse
        {{ $applications->links() }}
    </div></div>
</section>
@endsection