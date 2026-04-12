<section style="padding:48px 20px;background:{{ $theme['bg_page'] ?? '#f8fafc' }};">
    <div style="max-width:980px;margin:0 auto;">
        <h2 style="font-size:30px;margin:0 0 22px;color:{{ $theme['text_primary'] ?? '#111827' }};">
            {{ $settings['title'] ?? 'Testimonios' }}</h2>
        <div style="display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
            @foreach ($settings['items'] ?? [] as $item)
                <article
                    style="border:1px solid {{ $theme['neutral'] ?? '#e5e7eb' }};border-radius:12px;padding:16px;background:color-mix(in srgb, {{ $theme['primary'] ?? '#2563eb' }} 6%, {{ $theme['bg_card'] ?? '#ffffff' }});">
                    <p style="margin:0 0 12px;color:{{ $theme['text_primary'] ?? '#334155' }};line-height:1.6;">
                        “{{ $item['quote'] ?? '' }}”</p>
                    <p style="margin:0;font-weight:700;color:{{ $theme['text_primary'] ?? '#0f172a' }};">
                        {{ $item['author'] ?? '' }}</p>
                    @if (!empty($item['role'] ?? ''))
                        <p style="margin:2px 0 0;color:{{ $theme['text_secondary'] ?? '#64748b' }};font-size:13px;">
                            {{ $item['role'] }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
