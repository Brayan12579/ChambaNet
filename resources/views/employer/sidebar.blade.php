<aside class="sidebar panel" aria-label="Panel del empleador">
    <p class="eyebrow">ESPACIO EMPRESA</p>
    <h3>{{ auth()->user()->company->name }}</h3>
    <nav class="stack">
        <a class="{{ request()->routeIs('employer.dashboard') ? 'active' : '' }}" href="{{ route('employer.dashboard') }}">Resumen</a>
        <a class="{{ request()->routeIs('employer.company.*') ? 'active' : '' }}" href="{{ route('employer.company.edit') }}">Perfil de empresa</a>
        <a class="{{ request()->routeIs('employer.jobs.*') ? 'active' : '' }}" href="{{ route('employer.jobs.index') }}">Mis ofertas</a>
        <a href="{{ route('employer.jobs.create') }}">Publicar una oferta ↗</a>
    </nav>
    <p class="muted">Conecta con el talento de Cusco. Publica información clara y revisa cada candidatura.</p>
</aside>