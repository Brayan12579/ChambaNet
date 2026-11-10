<aside class="sidebar panel" aria-label="Panel de administración">
    <span class="eyebrow">ChambaNet</span>
    <h2>Administración</h2>
    <nav class="stack" aria-label="Secciones administrativas">
        <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')]) @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif><x-icon name="sliders" :size="19" />Resumen general</a>
        @foreach($sections as $key => $definition)
            <a href="{{ route('admin.content.index', ['section' => $key]) }}" @class(['active' => request()->route('section') === $key]) @if(request()->route('section') === $key) aria-current="page" @endif><x-icon name="{{ match($key) { 'users' => 'users', 'companies' => 'building', 'resources' => 'book', 'reports' => 'shield', 'districts' => 'pin', 'categories' => 'sliders', default => 'briefcase' } }}" :size="19" />{{ $definition[1] }}</a>
        @endforeach
        <a href="{{ route('admin.applications') }}" @class(['active' => request()->routeIs('admin.applications')]) @if(request()->routeIs('admin.applications')) aria-current="page" @endif><x-icon name="file" :size="19" />Postulaciones</a>
    </nav>
    <p class="muted">{{ auth()->user()->name === 'Administrador TrabajoGO' ? 'Administrador '.config('app.name') : auth()->user()->name }}<br><span class="badge">Administrador</span></p>
</aside>
