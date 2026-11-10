<aside class="sidebar panel" aria-label="Panel del empleador">
    <p class="eyebrow">ESPACIO EMPRESA</p>
    <div class="sidebar-identity"><span class="account-avatar" aria-hidden="true"><x-icon name="building" :size="23" /></span><h3>{{ auth()->user()->company->name }}<span class="sidebar-role">Empleador</span></h3></div>
    <nav class="stack" aria-label="Secciones de la empresa">
        @foreach(['employer.dashboard' => ['Resumen', 'building', request()->routeIs('employer.dashboard')], 'employer.jobs.index' => ['Mis ofertas', 'briefcase', request()->routeIs('employer.jobs.index', 'employer.jobs.edit')], 'employer.applications.index' => ['Postulaciones', 'users', request()->routeIs('employer.applications.*', 'employer.jobs.applicants')], 'employer.company.edit' => ['Perfil de empresa', 'file', request()->routeIs('employer.company.*')], 'employer.jobs.create' => ['Publicar oferta', 'arrow', request()->routeIs('employer.jobs.create')]] as $route => [$label, $icon, $active])
            <a href="{{ route($route) }}" @class(['active' => $active]) @if($active) aria-current="page" @endif><x-icon :name="$icon" :size="19" />{{ $label }}</a>
        @endforeach
    </nav>
    <p class="muted">Conecta con el talento de Cusco. Publica información clara y revisa cada candidatura.</p>
</aside>
