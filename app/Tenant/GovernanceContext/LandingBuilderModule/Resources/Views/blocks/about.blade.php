<section style="padding:56px 20px;background:{{ $theme['bg_page'] ?? '#f8fafc' }};">
    <div
        style="max-width:1040px;margin:0 auto;display:grid;gap:18px;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));align-items:center;">
        <div>
            <h2 style="font-size:32px;margin:0 0 12px;color:{{ $theme['text_primary'] ?? '#111827' }};">
                {{ $settings['title'] ?? 'Sobre nosotros' }}</h2>
            <p style="margin:0;line-height:1.7;color:{{ $theme['text_secondary'] ?? '#475569' }};">
                {{ $settings['body'] ?? '' }}</p>
        </div>
        @if (!empty($settings['image_url'] ?? null))
            <img src="{{ $settings['image_url'] }}" alt="{{ $settings['title'] ?? 'Sobre nosotros' }}"
                style="width:100%;border-radius:16px;border:1px solid {{ $theme['neutral'] ?? '#e5e7eb' }};object-fit:cover;max-height:320px;" />
        @endif
    </div>
</section>
