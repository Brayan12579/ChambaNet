@props(['completion'])
<section class="panel profile-completion" aria-label="Avance de tu perfil">
    <div class="page-heading"><div><span class="eyebrow">TU MEJOR PRESENTACIÓN</span><h2>Tu perfil, paso a paso</h2><p>{{ $completion['completed'] }} de {{ $completion['total'] }} campos completos. Cada dato te ayuda a presentar tu experiencia.</p></div><strong class="completion-percentage">{{ $completion['percentage'] }}%</strong></div>
    <progress class="profile-progress" max="100" value="{{ $completion['percentage'] }}" aria-label="Perfil completado">{{ $completion['percentage'] }}%</progress>
    @if($completion['missing'])
        <ul class="completion-suggestions">@foreach($completion['missing'] as $field => $item)<li><a href="{{ route('candidate.profile.edit') }}#{{ $field }}"><x-icon name="arrow" :size="16" /><span><strong>{{ $item['label'] }}</strong><small>{{ $item['tip'] }}</small></span></a></li>@endforeach</ul>
    @else
        <p class="completion-done"><x-icon name="check" :size="18" />Tu perfil está completo. Mantén tu CV actualizado.</p>
    @endif
    <p class="muted">El porcentaje se calcula con los datos guardados. No es un requisito para postular ni una evaluación de tu talento.</p>
</section>
