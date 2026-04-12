@php $items = is_array($settings['items'] ?? null) ? $settings['items'] : []; @endphp

<section style="padding:52px 20px;background:{{ $theme['bg_section'] ?? '#ffffff' }};">
    <div style="max-width:1100px;margin:0 auto;">
        <h2 style="font-size:30px;margin:0 0 20px;color:{{ $theme['text_primary'] ?? '#111827' }};">
            {{ $settings['title'] ?? 'Catálogo' }}</h2>
        <div style="display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
            @foreach ($items as $item)
                <article
                    style="border:1px solid {{ $theme['neutral'] ?? '#e5e7eb' }};border-radius:12px;padding:14px;background:{{ $theme['bg_card'] ?? '#ffffff' }};">
                    <h3 style="margin:0 0 6px;font-size:18px;color:{{ $theme['text_primary'] ?? '#0f172a' }};">
                        {{ $item['name'] ?? '' }}</h3>
                    <p style="margin:0 0 10px;font-weight:700;color:{{ $theme['primary'] ?? '#2563eb' }};">
                        {{ $item['price'] ?? '' }}</p>
                    <p style="margin:0;color:{{ $theme['text_secondary'] ?? '#475569' }};line-height:1.5;">
                        {{ $item['description'] ?? '' }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
