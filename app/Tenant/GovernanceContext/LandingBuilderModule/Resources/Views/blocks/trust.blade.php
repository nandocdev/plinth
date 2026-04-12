@php $items = is_array($settings['items'] ?? null) ? $settings['items'] : []; @endphp

<section style="padding:42px 20px;background:{{ $theme['bg_page'] ?? '#f8fafc' }};">
    <div style="max-width:1000px;margin:0 auto;">
        <p
            style="margin:0 0 12px;font-size:13px;font-weight:700;letter-spacing:.08em;color:{{ $theme['text_secondary'] ?? '#64748b' }};text-transform:uppercase;">
            {{ $settings['title'] ?? 'Confianza' }}</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            @foreach ($items as $item)
                <span
                    style="display:inline-flex;align-items:center;padding:8px 12px;border-radius:999px;border:1px solid {{ $theme['neutral'] ?? '#e5e7eb' }};background:{{ $theme['bg_card'] ?? '#ffffff' }};color:{{ $theme['text_primary'] ?? '#0f172a' }};font-size:13px;font-weight:600;">
                    {{ $item['title'] ?? '' }}
                </span>
            @endforeach
        </div>
    </div>
</section>
