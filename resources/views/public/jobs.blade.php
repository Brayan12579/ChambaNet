@extends('layouts.app')
@section('title', 'Explorar empleos en Cusco')
@section('content')
@php
    $activeFilters = [];
    foreach (['q', 'district', 'category', 'schedule', 'contract_type', 'salary_min', 'verified'] as $key) {
        if (!array_key_exists($key, $filters) || $filters[$key] === null || $filters[$key] === '' || ($key === 'verified' && !$filters[$key])) {
            continue;
        }
        $activeFilters[$key] = match ($key) {
            'q' => 'Puesto: '.$filters[$key],
            'district' => $districts->firstWhere('id', $filters[$key])?->name ?? 'Distrito',
            'category' => $categories->firstWhere('id', $filters[$key])?->name ?? 'Categoría',
            'schedule' => \App\Models\JobOffer::SCHEDULES[$filters[$key]] ?? 'Jornada',
            'contract_type' => \App\Models\JobOffer::CONTRACT_TYPES[$filters[$key]] ?? 'Contrato',
            'salary_min' => 'Desde S/ '.number_format((float) $filters[$key], 0),
            'verified' => 'Empresas verificadas',
        };
    }
@endphp
<section class="page-hero">
    <div class="container">
        <span class="eyebrow">OPORTUNIDADES CERCA DE TI</span>
        <h1>Tu próximo empleo<br>puede estar en Cusco<span class="heading-dot">.</span></h1>
        <p>Encuentra un puesto que encaje con tu experiencia, tu jornada y tu distrito.</p>
    </div>
</section>
<div class="container section">
    <button class="button button-secondary filter-toggle" type="button" data-filters-toggle aria-controls="job-filters" aria-expanded="false">
        <x-icon name="sliders" :size="20" /> Filtrar empleos
        @if(count($activeFilters))<span class="badge">{{ count($activeFilters) }}</span>@endif
        <x-icon name="arrow-down" :size="17" />
    </button>
    <div class="jobs-layout">
        <aside id="job-filters" class="filter-panel" data-filters-panel aria-labelledby="filters-heading">
            <div class="filter-title">
                <div><span class="eyebrow">ENCUENTRA LO QUE VA CONTIGO</span><h2 id="filters-heading">Afina tu búsqueda</h2></div>
                <button type="button" class="icon-button" data-filters-close aria-label="Cerrar filtros"><x-icon name="close" :size="22" /></button>
            </div>
            <form method="GET" action="{{ route('jobs.index') }}" class="stack" data-filters-form>
                <input type="hidden" name="sort" value="{{ $filters['sort'] ?? 'recent' }}">
                <div class="field"><label for="q">Puesto o palabra clave</label><input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Ej. atención al cliente" maxlength="120"></div>
                <div class="field"><label for="district">Distrito</label><select id="district" name="district"><option value="">Todo Cusco</option>@foreach($districts as $district)<option value="{{ $district->id }}" @selected(($filters['district'] ?? '') == $district->id)>{{ $district->name }}</option>@endforeach</select></div>
                <div class="field"><label for="category">Categoría</label><select id="category" name="category"><option value="">Todas las categorías</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                <div class="field"><label for="schedule">Jornada laboral</label><select id="schedule" name="schedule"><option value="">Todas las jornadas</option>@foreach(\App\Models\JobOffer::SCHEDULES as $value => $label)<option value="{{ $value }}" @selected(($filters['schedule'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="field"><label for="contract_type">Tipo de contrato</label><select id="contract_type" name="contract_type"><option value="">Todos los contratos</option>@foreach(\App\Models\JobOffer::CONTRACT_TYPES as $value => $label)<option value="{{ $value }}" @selected(($filters['contract_type'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="field"><label for="salary_min">Salario desde (S/ al mes)</label><input id="salary_min" name="salary_min" type="number" min="0" max="100000" step="1" value="{{ $filters['salary_min'] ?? '' }}" placeholder="Ej. 1400"></div>
                <label class="checkbox-label"><input type="checkbox" name="verified" value="1" @checked($filters['verified'] ?? false)> Solo empresas verificadas</label>
                <div class="filter-actions"><button class="button button-full" type="submit">Aplicar filtros <x-icon name="search" :size="18" /></button><a class="reset-link" href="{{ route('jobs.index') }}">Limpiar filtros</a></div>
            </form>
        </aside>
        <section class="jobs-results" aria-labelledby="results-title">
            <div class="results-toolbar">
                <div class="results-heading"><h2 id="results-title">{{ $jobs->total() }} {{ $jobs->total() === 1 ? 'empleo encontrado' : 'empleos encontrados' }}</h2><p class="muted">Oportunidades operativas en Cusco, Perú</p></div>
                <form method="GET" action="{{ route('jobs.index') }}" class="results-sort">
                    @foreach(\Illuminate\Support\Arr::except($filters, ['sort', 'page']) as $key => $value)
                        @if($value !== null && $value !== '')<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                    @endforeach
                    <label for="results-sort">Ordenar por</label>
                    <select id="results-sort" name="sort"><option value="recent" @selected(($filters['sort'] ?? 'recent') === 'recent')>Más recientes</option><option value="salary" @selected(($filters['sort'] ?? '') === 'salary')>Mayor salario</option></select>
                    <button type="submit" class="button button-secondary button-sm">Ordenar</button>
                </form>
            </div>
            @if(count($activeFilters))
                <div class="active-filters" aria-label="Filtros aplicados">
                    @foreach($activeFilters as $key => $label)
                        <a class="filter-chip" href="{{ route('jobs.index', \Illuminate\Support\Arr::except($filters, [$key, 'page'])) }}" aria-label="Quitar filtro {{ $label }}">{{ $label }}<x-icon name="close" :size="15" /></a>
                    @endforeach
                    <a class="text-link" href="{{ route('jobs.index') }}">Limpiar todos</a>
                </div>
            @endif
            <div class="demo-notice"><x-icon name="book" :size="19" /><span>Las ofertas marcadas <strong>Demo</strong> son ejemplos ficticios para explorar la plataforma.</span></div>
            <div class="grid jobs-grid">
                @forelse($jobs as $job)
                    <x-job-card :job="$job" />
                @empty
                    <div class="empty-state panel">
                        <span class="category-icon"><x-icon name="search" :size="34" /></span>
                        <h3>No encontramos coincidencias esta vez</h3>
                        <p>Prueba quitando un filtro o busca un puesto más general. Tu próxima oportunidad puede estar a un ajuste de distancia.</p>
                        <a class="button" href="{{ route('jobs.index') }}">Explorar todos los empleos <x-icon name="arrow" :size="18" /></a>
                    </div>
                @endforelse
            </div>
            @if($jobs->hasPages())<div class="pagination">{{ $jobs->links() }}</div>@endif
        </section>
    </div>
</div>
@endsection
