@extends('layouts.app')
@section('title', 'Supervisión de postulaciones')
@section('content')
<div class="container section dashboard-layout">
    @include('admin.sidebar')
    <div class="dashboard-main stack">
        <header class="page-heading"><div><span class="eyebrow">SUPERVISIÓN</span><h1>Postulaciones</h1><p class="muted">Revisa la actividad y el estado de los procesos de selección.</p></div><span class="badge">{{ number_format($applications->total()) }} registros</span></header>
        @if($applications->isNotEmpty())
            <div class="panel table-wrap"><table><caption class="sr-only">Supervisión de postulaciones</caption><thead><tr><th scope="col">Candidato</th><th scope="col">Oferta y empresa</th><th scope="col">Estado</th><th scope="col">Fecha</th></tr></thead><tbody>
                @foreach($applications as $application)<tr><td><strong>{{ $application->user->name }}</strong></td><td><a class="text-link" href="{{ route('admin.content.edit', ['section' => 'jobs', 'record' => $application->job_offer_id]) }}">{{ $application->jobOffer->title }}</a><div class="muted">{{ $application->jobOffer->company->name }}</div></td><td><x-application-status :status="$application->status" /></td><td class="muted">{{ $application->created_at->format('d/m/Y') }}</td></tr>@endforeach
            </tbody></table></div>
            <div class="pagination">{{ $applications->links() }}</div>
        @else
            <div class="panel empty-state"><x-icon name="users" :size="36" /><h2>Los procesos empiezan aquí</h2><p class="muted">Cuando un candidato postule a una oferta, podrás supervisar su estado.</p></div>
        @endif
    </div>
</div>
@endsection
