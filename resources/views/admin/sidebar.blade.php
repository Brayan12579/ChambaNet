<aside class="sidebar panel" aria-label="Panel de administración">
    <span class="eyebrow">ChambaNet</span>
    <h2>Administración</h2>
    <p class="muted">Gestiona la comunidad y cuida la calidad de las oportunidades.</p>
    <nav class="stack" aria-label="Secciones administrativas">
        <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')]) @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>Resumen general</a>
        @foreach($sections as $key => $definition)
            <a href="{{ route('admin.content.index', ['section' => $key]) }}" @class(['active' => request()->route('section') === $key]) @if(request()->route('section') === $key) aria-current="page" @endif>{{ $definition[1] }}</a>
        @endforeach
        <a href="{{ route('admin.applications') }}" @class(['active' => request()->routeIs('admin.applications')]) @if(request()->routeIs('admin.applications')) aria-current="page" @endif>Postulaciones</a>
    </nav>
    <p class="muted">{{ auth()->user()->name === 'Administrador TrabajoGO' ? 'Administrador '.config('app.name') : auth()->user()->name }}<br><span class="badge">Administrador</span></p>
</aside>
