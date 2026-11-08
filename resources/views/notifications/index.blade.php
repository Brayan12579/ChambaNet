@extends('layouts.app')
@section('title', 'Notificaciones')
@section('content')
<section class="container section">
    <header class="page-heading"><div><span class="eyebrow">TU ACTIVIDAD, EN UN SOLO LUGAR</span><h1>Notificaciones</h1><p>{{ $unreadCount }} {{ $unreadCount === 1 ? 'actualización sin leer' : 'actualizaciones sin leer' }}. Las novedades se guardan en tu cuenta.</p></div><a class="button button-secondary" href="{{ route('dashboard') }}">Volver a mi panel</a></header>
    <div class="panel actions notification-toolbar">
        <a class="button {{ $filter === '' ? '' : 'button-ghost' }}" href="{{ route('notifications.index') }}" @if($filter === '') aria-current="page" @endif>Todas</a>
        <a class="button {{ $filter === 'unread' ? '' : 'button-ghost' }}" href="{{ route('notifications.index', ['filter' => 'unread']) }}" @if($filter === 'unread') aria-current="page" @endif>Sin leer @if($unreadCount)<span class="badge">{{ $unreadCount }}</span>@endif</a>
        @if($unreadCount)<form method="POST" action="{{ route('notifications.read-all') }}">@csrf @method('PATCH')<button class="button button-secondary">Marcar todas como leídas</button></form>@endif
    </div>
    <div class="notification-list stack">
        @forelse($notifications as $notification)
            <article class="panel notification-item {{ $notification->read_at ? '' : 'is-unread' }}">
                <div class="page-heading"><div><span class="badge {{ $notification->read_at ? 'badge-neutral' : 'badge-info' }}">{{ $notification->read_at ? 'Leída' : 'Nueva' }}</span>@if($notification->data['is_demo'] ?? false)<span class="badge badge-demo">Demostración</span>@endif<h2>{{ $notification->data['title'] ?? 'Actualización de tu cuenta' }}</h2><p>{{ $notification->data['message'] ?? '' }}</p><time class="notification-date" datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->locale('es')->diffForHumans() }} · {{ $notification->created_at->format('d/m/Y H:i') }}</time></div></div>
                <div class="actions">@if($notification->action_url)<a class="button button-secondary" href="{{ $notification->action_url }}">Ver proceso <x-icon name="arrow" :size="18" /></a>@endif
                @unless($notification->read_at)<form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf @method('PATCH')<button class="button button-ghost">Marcar como leída</button></form>@endunless</div>
            </article>
        @empty
            <div class="panel empty-state"><x-icon name="bell" :size="36" /><h2>{{ $filter === 'unread' ? 'Estás al día' : 'Aquí llegarán tus novedades' }}</h2><p>{{ $filter === 'unread' ? 'No tienes notificaciones pendientes de lectura.' : 'Te avisaremos cuando haya actividad en tus postulaciones o publicaciones.' }}</p><a class="button button-secondary" href="{{ route('dashboard') }}">Ir a mi panel</a></div>
        @endforelse
    </div>
    <div class="pagination">{{ $notifications->links() }}</div>
    <p class="muted">El contador se actualiza al cargar la página. Estas notificaciones son internas; no implican un envío de correo electrónico.</p>
</section>
@endsection
