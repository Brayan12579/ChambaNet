@extends('layouts.app')
@section('title', 'Perfil de empresa')
@section('content')
<section class="section container">
    <div class="page-heading"><div><p class="eyebrow">PRESENTA TU NEGOCIO</p><h1>Tu empresa en ChambaNet</h1><p class="muted">Esta información se mostrará a quienes buscan empleo.</p></div></div>
    <div class="dashboard-layout">@include('employer.sidebar')<div class="dashboard-main panel">
        <div class="company-form-heading"><div class="company-avatar company-avatar-lg">@if($company->logo_path)<img src="{{ route('companies.logo', $company) }}" alt="Logo de {{ $company->name }}">@else<x-icon name="building" :size="30" />@endif</div><div><h2>El perfil de tu negocio</h2><span class="badge {{ $company->verified ? 'badge-success' : 'badge-warning' }}">{{ $company->verified ? 'Verificada por administración' : 'Pendiente de verificación' }}</span></div></div>
        <form action="{{ route('employer.company.update') }}" method="POST" enctype="multipart/form-data" class="stack">@csrf @method('PUT')
            <div class="form-grid"><div class="field"><label for="name">Nombre comercial *</label><input id="name" name="name" required maxlength="160" value="{{ old('name', $company->name) }}"></div>
                <div class="field"><label for="sector">Rubro *</label><input id="sector" name="sector" required maxlength="120" value="{{ old('sector', $company->sector) }}" placeholder="Ej. Restaurantes y cafeterías"></div>
                <div class="field"><label for="district_id">Distrito *</label><select id="district_id" name="district_id" required><option value="">Selecciona un distrito</option>@foreach($districts as $district)<option value="{{ $district->id }}" @selected(old('district_id', $company->district_id) == $district->id)>{{ $district->name }}</option>@endforeach</select></div>
                <div class="field"><label for="address">Dirección *</label><input id="address" name="address" required maxlength="255" value="{{ old('address', $company->address) }}"></div>
            </div>
            <div class="field"><label for="description">Acerca de la empresa *</label><textarea id="description" name="description" required minlength="20" maxlength="5000" rows="6">{{ old('description', $company->description) }}</textarea></div>
            <div class="form-grid"><div class="field"><label for="website">Sitio web</label><input id="website" name="website" type="url" placeholder="https://..." value="{{ old('website', $company->website) }}"></div><div class="field"><label for="logo">Logo de tu empresa</label><input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp"><small class="muted">JPG, PNG o WebP. Máximo 2 MB.</small></div></div>
            <div class="actions"><button class="button" type="submit">Guardar cambios</button><a class="button-secondary" href="{{ route('companies.show', $company) }}">Ver perfil público</a></div>
        </form>
    </div></div>
</section>
@endsection
