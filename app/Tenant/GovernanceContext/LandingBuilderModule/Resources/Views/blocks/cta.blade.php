<section
    style="padding:56px 20px;background:linear-gradient(135deg, {{ $theme['primary'] ?? '#2563eb' }}, {{ $theme['accent'] ?? '#0f172a' }});color:white;text-align:center;">
    <div style="max-width:860px;margin:0 auto;">
        <h2 style="font-size:36px;margin:0 0 12px;">{{ $settings['title'] ?? '¿Listo para empezar?' }}</h2>
        <p style="margin:0 0 22px;color:#cbd5e1;line-height:1.6;">{{ $settings['subtitle'] ?? '' }}</p>
        <a href="{{ $settings['button_url'] ?? '/register' }}"
            style="display:inline-block;padding:12px 24px;border-radius:10px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22);color:white;text-decoration:none;font-weight:700;backdrop-filter:blur(10px);">
            {{ $settings['button_text'] ?? 'Crear cuenta' }}
        </a>
    </div>
</section>
