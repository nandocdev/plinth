<section
    style="padding:72px 20px;background:radial-gradient(circle at top left, {{ $theme['neutral'] ?? '#e2e8f0' }} 0%, {{ $theme['bg_page'] ?? '#f8fafc' }} 55%);text-align:center;">
    <div style="max-width:920px;margin:0 auto;">
        <h1 style="font-size:46px;line-height:1.1;margin:0 0 16px;color:{{ $theme['text_primary'] ?? '#0f172a' }};">
            {{ $settings['headline'] ?? 'Bienvenido' }}</h1>
        <p
            style="font-size:18px;line-height:1.6;max-width:760px;margin:0 auto 28px;color:{{ $theme['text_secondary'] ?? '#475569' }};">
            {{ $settings['subheadline'] ?? '' }}
        </p>
        <a href="{{ $settings['cta_url'] ?? '/register' }}"
            style="display:inline-block;padding:12px 24px;border-radius:10px;background:linear-gradient(135deg, {{ $theme['primary'] }}, {{ $theme['accent'] ?? '#0f172a' }});color:white;text-decoration:none;font-weight:700;box-shadow:0 18px 36px color-mix(in srgb, {{ $theme['primary'] }} 24%, transparent);">
            {{ $settings['cta_text'] ?? 'Comenzar' }}
        </a>
    </div>
</section>
