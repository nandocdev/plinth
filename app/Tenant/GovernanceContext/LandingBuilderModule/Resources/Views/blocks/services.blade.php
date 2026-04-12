<section style="padding:48px 20px;background:{{ $theme['bg_section'] ?? '#ffffff' }};">
    <div style="max-width:980px;margin:0 auto;">
        <h2 style="font-size:30px;margin:0 0 22px;color:{{ $theme['text_primary'] ?? '#111827' }};">
            {{ $settings['title'] ?? 'Servicios' }}</h2>
        <div style="display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
            @foreach ($settings['items'] ?? [] as $item)
                <article
                    style="border:1px solid {{ $theme['neutral'] ?? '#e5e7eb' }};border-radius:12px;padding:16px;background:{{ $theme['bg_card'] ?? '#ffffff' }};">
                    <h3 style="margin:0 0 8px;font-size:18px;color:{{ $theme['text_primary'] ?? '#0f172a' }};">
                        {{ $item['title'] ?? '' }}</h3>
                    <p style="margin:0;color:{{ $theme['text_secondary'] ?? '#475569' }};line-height:1.5;">
                        {{ $item['description'] ?? '' }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
