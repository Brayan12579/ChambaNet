<aside class="sidebar panel" aria-label="Panel del candidato">
    <span class="eyebrow">MI ESPACIO</span>
    <h3>{{ auth()->user()->name }}</h3>
    <nav class="stack">
        <a class="{{ request()->routeIs('candidate.dashboard') ? 'active' : '' }}" href="{{ route('candidate.dashboard') }}">Resumen</a>
        <a class="{{ request()->routeIs('candidate.profile.*') ? 'active' : '' }}" href="{{ route('candidate.profile.edit') }}">Mi perfil y CV</a>
        <a class="{{ request()->routeIs('candidate.applications.*') ? 'active' : '' }}" href="{{ route('candidate.applications.index') }}">Mis postulaciones</a>
        <a class="{{ request()->routeIs('candidate.favorites.*') ? 'active' : '' }}" href="{{ route('candidate.favorites.index') }}">Empleos guardados</a>
        <a class="button button-secondary" href="{{ route('jobs.index') }}">Buscar empleo</a>
    </nav>
</aside>
