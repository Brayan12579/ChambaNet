@extends('layouts.app')
@section('title', 'Mi perfil y CV')
@section('content')
<section class="container section">
    <div class="page-heading"><div><span class="eyebrow">DA A CONOCER TU TALENTO</span><h1>Mi perfil y CV</h1><p class="muted">Cuenta lo que sabes hacer. Cada experiencia suma.</p></div></div>
    <div class="dashboard-layout">
        @include('candidate._nav')
        <div class="dashboard-main stack">
            <x-profile-completion :completion="$completion" />
            <form class="panel stack" method="POST" action="{{ route('candidate.profile.update') }}" enctype="multipart/form-data" data-loading-form>
                @csrf @method('PUT')
                <h2>Información personal</h2>
                <div class="form-grid">
                    <div class="field"><label for="name">Nombre completo</label><input id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required maxlength="120" autocomplete="name">@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="email">Correo electrónico</label><input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required maxlength="190" autocomplete="email">@error('email')<small class="field-error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="phone">Teléfono <span class="muted">(opcional)</span></label><input id="phone" name="phone" value="{{ old('phone', $profile?->phone) }}" maxlength="25" autocomplete="tel">@error('phone')<small class="field-error">{{ $message }}</small>@enderror</div>
                    <div class="field"><label for="district_id">Distrito</label><select id="district_id" name="district_id"><option value="">Selecciona tu distrito</option>@foreach($districts as $district)<option value="{{ $district->id }}" @selected(old('district_id', $profile?->district_id) == $district->id)>{{ $district->name }}</option>@endforeach</select>@error('district_id')<small class="field-error">{{ $message }}</small>@enderror</div>
                </div>
                <div class="field"><label for="bio">Sobre mí</label><textarea id="bio" name="bio" rows="3" maxlength="2000" placeholder="Cuéntanos qué tipo de trabajo buscas y tus principales habilidades.">{{ old('bio', $profile?->bio) }}</textarea>@error('bio')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field"><label for="experience">Experiencia laboral</label><textarea id="experience" name="experience" rows="5" maxlength="5000" placeholder="Incluye empleos anteriores, prácticas o actividades que te hayan ayudado a desarrollar habilidades.">{{ old('experience', $profile?->experience) }}</textarea>@error('experience')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="field"><label for="cv">{{ $cvInfo['exists'] ? 'Reemplazar mi CV en PDF' : 'Subir mi CV en PDF' }}</label><input id="cv" name="cv" type="file" accept="application/pdf,.pdf"><small class="muted">Máximo 5 MB. Tu documento es privado y solo lo verán los empleadores a los que postules. Al subir otro CV, las postulaciones anteriores conservan su documento original.</small>@error('cv')<small class="field-error">{{ $message }}</small>@enderror</div>
                <div class="actions"><button class="button" type="submit">Guardar cambios</button>@if($cvInfo['exists'])<a class="button button-secondary" target="_blank" rel="noopener" href="{{ route('candidate.cv') }}">Ver mi CV ↗</a>@endif</div>
            </form>
            @if($cvInfo['exists'])
                <div class="panel cv-panel">
                    <div class="page-heading"><div><span class="eyebrow">ARCHIVO PRIVADO</span><h2>Tu CV está listo</h2><p>PDF · {{ $cvInfo['sizeKb'] }} KB · Actualizado el {{ $cvInfo['updatedAt']->format('d/m/Y') }}</p></div>
                        <div class="actions">
                            <a class="button button-secondary" href="{{ route('candidate.cv') }}" target="_blank" rel="noopener">Abrir PDF ↗</a>
                            <form method="POST" action="{{ route('candidate.cv.destroy') }}" data-confirm="¿Eliminar el CV de tu perfil? Las postulaciones anteriores conservarán su documento." data-confirm-title="Eliminar CV" data-confirm-button="Sí, eliminar CV">
                                @csrf @method('DELETE')
                                <button class="button button-danger" type="submit"><x-icon name="trash" :size="18" />Eliminar CV</button>
                            </form>
                        </div>
                    </div>
                    <iframe src="{{ route('candidate.cv') }}" title="Vista privada de mi currículum PDF" class="cv-preview" width="100%" height="500" loading="lazy"></iframe><p class="muted">Si tu navegador no muestra el PDF, utiliza «Ver mi CV».</p>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
