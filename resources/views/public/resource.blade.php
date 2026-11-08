@extends('layouts.app')
@section('title', $resource->title)
@section('content')
<div class="container article-container section"><a class="back-link" href="{{ route('resources.index') }}">← Volver a recursos</a><span class="eyebrow">ORIENTACIÓN LABORAL</span><h1>{{ $resource->title }}</h1><p class="article-excerpt">{{ $resource->excerpt }}</p><div class="article-meta"><x-icon name="book" :size="16" />{{ max(2, (int) ceil(str_word_count($resource->body) / 180)) }} minutos de lectura <span>·</span> Equipo ChambaNet</div><article class="panel article-body prose-content">{{ $resource->body }}</article><div class="article-end"><h2>¿Listo para tu siguiente paso?</h2><p>Encuentra oportunidades que vayan contigo en Cusco.</p><a class="button" href="{{ route('jobs.index') }}">Explorar empleos <x-icon name="arrow" :size="17" /></a></div></div>
@endsection
