@php $items = is_array($settings['items'] ?? null) ? $settings['items'] : []; @endphp

<section style="padding:52px 20px;background:{{ $theme['bg_page'] ?? '#f8fafc' }};">
    <div style="max-width:960px;margin:0 auto;">
        <h2 style="font-size:30px;margin:0 0 18px;color:{{ $theme['text_primary'] ?? '#111827' }};">
            {{ $settings['title'] ?? 'Logros' }}</h2>
        <div style="display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));">
            @foreach ($items as $item)
                <article
                    style="border:1px solid {{ $theme['neutral'] ?? '#e5e7eb' }};border-radius:12px;padding:14px;background:{{ $theme['bg_card'] ?? '#ffffff' }};">
                    <p style="margin:0;font-size:12px;color:{{ $theme['text_secondary'] ?? '#64748b' }};">
                        {{ $item['title'] ?? '' }}</p>
                    <p style="margin:6px 0 0;font-size:28px;font-weight:700;color:{{ $theme['primary'] ?? '#2563eb' }};">
                        {{ $item['value'] ?? '' }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
