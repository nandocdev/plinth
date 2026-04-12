<section style="padding:56px 20px;background:{{ $theme['bg_section'] ?? '#ffffff' }};">
    <div
        style="max-width:900px;margin:0 auto;border:1px solid {{ $theme['neutral'] ?? '#e5e7eb' }};border-radius:16px;padding:24px;background:{{ $theme['bg_card'] ?? '#ffffff' }};">
        <h2 style="font-size:30px;margin:0 0 16px;color:{{ $theme['text_primary'] ?? '#111827' }};">
            {{ $settings['title'] ?? 'Contacto' }}</h2>
        @if (!empty($settings['email'] ?? null))
            <p style="margin:0 0 6px;color:{{ $theme['text_secondary'] ?? '#475569' }};">Email: {{ $settings['email'] }}
            </p>
        @endif
        @if (!empty($settings['phone'] ?? null))
            <p style="margin:0 0 6px;color:{{ $theme['text_secondary'] ?? '#475569' }};">Teléfono:
                {{ $settings['phone'] }}</p>
        @endif
        @if (!empty($settings['address'] ?? null))
            <p style="margin:0;color:{{ $theme['text_secondary'] ?? '#475569' }};">Dirección: {{ $settings['address'] }}
            </p>
        @endif
    </div>
</section>
