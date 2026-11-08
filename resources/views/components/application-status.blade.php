@props(['status', 'timeline' => false, 'showBadge' => true])
@php
    $labels = \App\Models\Application::STATUSES;
    $tone = match ($status) { 'accepted' => 'success', 'rejected' => 'danger', 'reviewing' => 'info', 'withdrawn' => 'neutral', default => 'warning' };
    $reviewed = in_array($status, ['reviewing', 'accepted', 'rejected'], true);
    $resolved = in_array($status, ['accepted', 'rejected'], true);
@endphp
@if($showBadge)
<span class="badge badge-{{ $tone }} application-state"><span class="status-dot" aria-hidden="true"></span>{{ $labels[$status] ?? $status }}</span>
@endif
@if($timeline)
    @if($status === 'withdrawn')
        <p class="application-withdrawn"><x-icon name="clock" :size="18" />Retiraste esta postulación. El proceso está cerrado para tu cuenta.</p>
    @else
        <ol class="application-timeline" aria-label="Seguimiento de la postulación">
            <li class="{{ $status === 'pending' ? 'is-current' : 'is-complete' }}" @if($status === 'pending') aria-current="step" @endif><span class="timeline-marker" aria-hidden="true">1</span><span>Enviada<small>Pendiente</small></span></li>
            <li class="{{ $status === 'reviewing' ? 'is-current' : ($resolved ? 'is-complete' : '') }}" @if($status === 'reviewing') aria-current="step" @endif><span class="timeline-marker" aria-hidden="true">2</span><span>En revisión<small>{{ $reviewed ? 'Revisión iniciada' : 'Por revisar' }}</small></span></li>
            <li class="{{ $resolved ? 'is-current' : '' }} {{ $status === 'rejected' ? 'is-rejected' : '' }}" @if($resolved) aria-current="step" @endif><span class="timeline-marker" aria-hidden="true">3</span><span>{{ $resolved ? $labels[$status] : 'Respuesta' }}<small>{{ $resolved ? 'Decisión registrada' : 'A la espera' }}</small></span></li>
        </ol>
    @endif
@endif
