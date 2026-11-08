@extends('layouts.app')
@section('title', 'Recursos para tu búsqueda laboral')
@section('content')
<div class="page-hero"><div class="container"><span class="eyebrow">PREPÁRATE PARA LO QUE VIENE</span><h1>El conocimiento también abre puertas<span class="heading-dot">.</span></h1><p>Guías sencillas para presentar tu talento y dar tu próximo paso con confianza.</p></div></div><div class="container section"><div class="grid-3">@forelse($resources as $resource)<x-resource-card :resource="$resource" :index="$loop->index" />@empty<div class="empty-state"><x-icon name="book" :size="40" /><h2>Estamos preparando nuevos recursos</h2><p>Mientras tanto, puedes explorar las oportunidades disponibles.</p><a class="button" href="{{ route('jobs.index') }}">Buscar empleo</a></div>@endforelse</div><div class="pagination">{{ $resources->links() }}</div></div>
@endsection
