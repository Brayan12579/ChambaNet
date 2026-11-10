<aside class="sidebar panel" aria-label="Panel del candidato">
    <span class="eyebrow">MI ESPACIO</span>
    <div class="sidebar-identity"><span class="account-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><h3>{{ auth()->user()->name }}<span class="sidebar-role">Candidato</span></h3></div>
    <nav class="stack" aria-label="Secciones del candidato">
        @foreach(['candidate.dashboard' => ['Resumen', 'briefcase', 'candidate.dashboard'], 'candidate.applications.index' => ['Mis postulaciones', 'file', 'candidate.applications.*'], 'candidate.favorites.index' => ['Empleos guardados', 'bookmark', 'candidate.favorites.*'], 'candidate.profile.edit' => ['Mi perfil y CV', 'users', 'candidate.profile.*']] as $route => [$label, $icon, $pattern])
            <a href="{{ route($route) }}" @class(['active' => request()->routeIs($pattern)]) @if(request()->routeIs($pattern)) aria-current="page" @endif><x-icon :name="$icon" :size="19" />{{ $label }}</a>
        @endforeach
        <a class="button button-secondary" href="{{ route('jobs.index') }}"><x-icon name="search" :size="19" />Buscar empleo</a>
    </nav>
</aside>
