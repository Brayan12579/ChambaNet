@props(['resource', 'index' => 0])
<article class="resource-card">
    <a class="resource-art resource-art-{{ $index % 3 }}" href="{{ route('resources.show', $resource) }}" tabindex="-1" aria-hidden="true"><span class="resource-art-lines"></span><x-icon :name="['book', 'users', 'shield'][$index % 3]" :size="58" /><span class="resource-art-dot"></span></a>
    <div class="resource-card-body"><span class="eyebrow">ORIENTACIÓN LABORAL · {{ max(2, (int) ceil(str_word_count($resource->body) / 180)) }} MIN</span><h3><a href="{{ route('resources.show', $resource) }}">{{ $resource->title }}</a></h3><p>{{ $resource->excerpt }}</p><a class="text-link" href="{{ route('resources.show', $resource) }}">Leer guía <x-icon name="arrow" :size="19" /></a></div>
</article>
