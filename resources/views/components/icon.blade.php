@props(['name' => 'arrow', 'size' => 20])
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes }}>
@switch($name)
@case('search') <circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/> @break
@case('pin') <path d="M20 10c0 6-8 11-8 11S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/> @break
@case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
@case('briefcase') <rect x="3" y="7" width="18" height="14" rx="3"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12c6 4 12 4 18 0M12 12v4"/> @break
@case('check') <path d="m5 12 4 4L19 6"/> @break
@case('shield') <path d="m12 3 8 3v6c0 4-8 9-8 9s-8-5-8-9V6l8-3Z"/><path d="m8 12 3 3 5-6"/> @break
@case('bookmark') <path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16l-6-4-6 4Z"/> @break
@case('building') <path d="M4 21V4h12v17M16 10h4v11M2 21h20M8 8h4M8 12h4M8 16h4"/> @break
@case('users') <circle cx="9" cy="8" r="3"/><path d="M2 21v-3a7 7 0 0 1 14 0v3M16 4a4 4 0 0 1 0 8M19 15a5 5 0 0 1 3 5"/> @break
@case('book') <path d="M12 5C7 2 3 4 3 4v15s5-2 9 1c4-3 9-1 9-1V4s-4-2-9 1Zm0 0v15"/> @break
@case('coffee') <path d="M4 8h13v7a6 6 0 0 1-12 0V8Zm13 1h2a3 3 0 0 1 0 6h-2M7 2v2M12 2v2M2 22h18"/> @break
@case('cart') <path d="M3 3h2l3 13h11l2-9H6M9 21h.01M18 21h.01"/> @break
@case('truck') <path d="M2 5h12v12H2zM14 9h4l4 5v3h-8M7 20a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM18 20a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/> @break
@case('spark') <path d="m12 2 3 7 7 3-7 3-3 7-3-7-7-3 7-3 3-7Z"/> @break
@case('mail') <rect x="3" y="5" width="18" height="14" rx="3"/><path d="m3 7 9 6 9-6"/> @break
@case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
@case('lock') <rect x="4" y="10" width="16" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/> @break
@case('arrow-down') <path d="m6 9 6 6 6-6"/> @break
@case('close') <path d="m6 6 12 12M18 6 6 18"/> @break
@case('sliders') <path d="M4 7h9m4 0h3M4 17h3m4 0h9"/><circle cx="15" cy="7" r="2"/><circle cx="9" cy="17" r="2"/> @break
@case('bell') <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/> @break
@case('file') <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Zm0 0v6h6M8 13h8M8 17h5"/> @break
@case('eye') <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/> @break
@default <path d="M4 12h16m-6-6 6 6-6 6"/>
@endswitch
</svg>
