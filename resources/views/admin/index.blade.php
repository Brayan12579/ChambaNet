@extends('layouts.app')
@section('title', $title.' · Administración')
@section('content')
@php
    $filters = $filters ?? ['q' => $search];
    $filterOptions = $filterOptions ?? [];
    $hasFilters = collect($filters)->except('sort')->contains(fn ($value) => $value !== null && $value !== '');
@endphp
<div class="container section dashboard-layout">
    @include('admin.sidebar')
    <div class="dashboard-main stack">
        <header class="page-heading"><div><span class="eyebrow">ADMINISTRACIÓN</span><h1>{{ $title }}</h1><p>{{ number_format($records->total()) }} {{ $records->total() === 1 ? 'registro encontrado' : 'registros encontrados' }}.</p></div>@if($section !== 'reports')<a class="button" href="{{ route('admin.content.create', ['section' => $section]) }}">Crear registro <x-icon name="arrow" :size="18" /></a>@endif</header>
        <form class="panel stack" method="GET" action="{{ route('admin.content.index', ['section' => $section]) }}" role="search">
            <div class="form-grid">
                <div class="field"><label for="admin-search">Buscar en {{ mb_strtolower($title) }}</label><input id="admin-search" type="search" name="q" value="{{ $search }}" maxlength="160" placeholder="Nombre, título o motivo"></div>
                @foreach($filterOptions as $key => $configuration)<div class="field"><label for="admin-filter-{{ $key }}">{{ $configuration['label'] }}</label><select name="{{ $key }}" id="admin-filter-{{ $key }}"><option value="">Todos</option>@foreach($configuration['options'] as $value => $label)<option value="{{ $value }}" @selected(array_key_exists($key, $filters) && (string) $filters[$key] === (string) $value)>{{ $label }}</option>@endforeach</select></div>@endforeach
                <div class="field"><label for="admin-sort">Orden</label><select id="admin-sort" name="sort"><option value="recent" @selected(($filters['sort'] ?? 'recent') === 'recent')>Más recientes</option><option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Más antiguos</option></select></div>
            </div>
            <div class="actions"><button class="button button-secondary" type="submit"><x-icon name="search" :size="18" />Aplicar búsqueda</button>@if($hasFilters)<a class="button button-ghost" href="{{ route('admin.content.index', ['section' => $section]) }}">Limpiar filtros</a>@endif</div>
        </form>
        @if($hasFilters)
            <div class="active-filters" aria-label="Filtros administrativos activos">
                @foreach(\Illuminate\Support\Arr::except($filters, ['sort']) as $key => $value)
                    @if($value !== null && $value !== '')
                        @php $label = $key === 'q' ? 'Búsqueda: '.$value : (($filterOptions[$key]['label'] ?? $key).': '.($filterOptions[$key]['options'][$value] ?? $value)); @endphp
                        <a class="filter-chip" href="{{ route('admin.content.index', ['section' => $section, ...\Illuminate\Support\Arr::except($filters, [$key, 'page'])]) }}" aria-label="Quitar filtro {{ $label }}">{{ $label }}<x-icon name="close" :size="15" /></a>
                    @endif
                @endforeach
            </div>
        @endif
        @if($section === 'companies')<div class="alert"><x-icon name="shield" :size="23" /><span>Revisa la identidad, la dirección y la información comercial antes de verificar. Las empresas DEMO son ficticias.</span></div>@endif
        @if($records->isNotEmpty())
            <div class="table-wrap" tabindex="0" role="region" aria-label="Tabla de {{ mb_strtolower($title) }}, desplazable en pantallas pequeñas">
                <table><caption class="sr-only">Listado de {{ mb_strtolower($title) }}</caption><thead><tr><th scope="col">Registro</th><th scope="col">Estado</th><th scope="col">Actualizado</th><th scope="col">Acciones</th></tr></thead><tbody>
                @foreach($records as $record)
                    @php
                        $isExpired = $section === 'jobs' && $record->status === 'published' && $record->expires_at?->isBefore(today());
                        $state = match($section) {
                            'users', 'categories', 'districts' => $record->active ? 'Activo' : 'Inactivo',
                            'companies' => $record->verified ? 'Verificada' : 'Pendiente de verificación',
                            'resources' => $record->published ? 'Publicado' : 'Borrador',
                            'jobs' => $isExpired ? 'Vencida' : (\App\Models\JobOffer::STATUSES[$record->status] ?? $record->status),
                            'reports' => \App\Models\Report::STATUSES[$record->status] ?? $record->status,
                            default => '—',
                        };
                        $tone = match($section) {
                            'users', 'categories', 'districts' => $record->active ? 'badge-success' : 'badge-neutral',
                            'companies' => $record->verified ? 'badge-success' : 'badge-warning',
                            'resources' => $record->published ? 'badge-success' : 'badge-neutral',
                            'jobs' => $isExpired ? 'badge-warning' : ($record->status === 'published' ? 'badge-success' : 'badge-neutral'),
                            'reports' => $record->status === 'pending' ? 'badge-danger' : ($record->status === 'reviewed' ? 'badge-success' : 'badge-neutral'),
                            default => 'badge-neutral',
                        };
                        $reason = $section === 'reports' ? match($record->reason) { 'fraud' => 'Posible fraude', 'inappropriate' => 'Contenido inapropiado', 'misleading' => 'Información engañosa', 'other' => 'Otro motivo', default => $record->reason } : null;
                    @endphp
                    <tr>
                        <td><strong>{{ $record->name ?? $record->title ?? $reason }}</strong>@if($record->is_demo)<span class="badge badge-demo">Demo</span>@endif
                            @if($section === 'users')<div class="muted">{{ $record->email }} · {{ \App\Models\User::ROLES[$record->role] ?? $record->role }}</div>
                            @elseif($section === 'companies')<div class="muted">{{ $record->sector ?: 'Rubro por completar' }}</div>
                            @elseif($section === 'jobs')<div class="muted">{{ $record->company->name }}</div>@if($record->expires_at)<div class="muted">Cierre {{ $record->expires_at->format('d/m/Y') }}</div>@endif
                            @elseif($section === 'reports')<div class="muted">{{ $record->jobOffer->title }} · {{ $record->user->name }}</div><p>{{ \Illuminate\Support\Str::limit($record->details, 130) }}</p>
                            @elseif($section === 'resources')<div class="muted">{{ \Illuminate\Support\Str::limit($record->excerpt, 100) }}</div>@endif
                        </td>
                        <td><span class="badge {{ $tone }}">{{ $state }}</span></td>
                        <td>{{ $record->updated_at->format('d/m/Y') }}</td>
                        <td><div class="actions">
                            <a class="button button-secondary button-sm" href="{{ route('admin.content.edit', ['section' => $section, 'record' => $record->id]) }}">{{ $section === 'reports' ? 'Revisar reporte' : 'Editar' }}</a>
                            @if($section === 'companies' && !$record->verified && $record->name && $record->description && $record->sector && $record->address && $record->district_id)<form action="{{ route('admin.companies.verify', $record) }}" method="POST" data-confirm="¿Verificar a {{ $record->name }}? Confirma que revisaste manualmente sus datos y su identidad.">@csrf @method('PATCH')<button class="button button-sm" type="submit">Verificar</button></form>@endif
                            @if($section === 'jobs' && $record->status !== 'closed')<form action="{{ route('admin.jobs.close', $record) }}" method="POST" data-confirm="¿Cerrar la oferta {{ $record->title }}? Se conservará su historial y dejará de recibir postulaciones.">@csrf @method('PATCH')<button class="button button-ghost button-sm" type="submit">Cerrar oferta</button></form>@endif
                            @unless($section === 'users' && $record->role === 'admin')<form method="POST" action="{{ route('admin.content.destroy', ['section' => $section, 'record' => $record->id]) }}" data-confirm="¿Eliminar este registro? Esta acción no se puede deshacer. Si tiene historial relacionado, se conservará.">@csrf @method('DELETE')<button class="button button-danger button-sm" type="submit">Eliminar</button></form>@endunless
                        </div></td>
                    </tr>
                @endforeach
                </tbody></table>
            </div>
            @if($records->hasPages())<div class="pagination">{{ $records->links() }}</div>@endif
        @else
            <div class="panel empty-state"><x-icon name="search" :size="35" /><h2>{{ $hasFilters ? 'No encontramos coincidencias' : 'Todavía no hay registros' }}</h2><p>{{ $hasFilters ? 'Prueba con otro término o elimina un filtro para ampliar los resultados.' : 'Los nuevos registros aparecerán en esta sección.' }}</p>@if($hasFilters)<a class="button button-secondary" href="{{ route('admin.content.index', ['section' => $section]) }}">Ver todos los registros</a>@endif</div>
        @endif
    </div>
</div>
@endsection
