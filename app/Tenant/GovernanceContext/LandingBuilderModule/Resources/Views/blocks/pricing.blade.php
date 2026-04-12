@php
    $plans = is_array($settings['plans'] ?? null) ? $settings['plans'] : [];
    $currency = $settings['currency'] ?? '$';
@endphp

<section style="padding:56px 20px;background:{{ $theme['bg_section'] ?? '#ffffff' }};">
    <div style="max-width:1100px;margin:0 auto;">
        <h2 style="font-size:32px;margin:0 0 22px;color:{{ $theme['text_primary'] ?? '#111827' }};">
            {{ $settings['title'] ?? 'Planes' }}</h2>
        <div style="display:grid;gap:14px;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
            @foreach ($plans as $plan)
                <article
                    style="border:1px solid {{ $theme['neutral'] ?? '#e5e7eb' }};border-radius:14px;padding:18px;background:{{ $theme['bg_card'] ?? '#ffffff' }};">
                    <h3 style="margin:0 0 8px;font-size:20px;color:{{ $theme['text_primary'] ?? '#0f172a' }};">
                        {{ $plan['name'] ?? 'Plan' }}</h3>
                    <p
                        style="margin:0 0 12px;font-size:28px;font-weight:700;color:{{ $theme['primary'] ?? '#2563eb' }};">
                        {{ $currency }}{{ $plan['price'] ?? '0' }}<span
                            style="font-size:13px;color:{{ $theme['text_secondary'] ?? '#64748b' }};">/{{ $plan['period'] ?? 'mes' }}</span>
                    </p>
                    <a href="#"
                        style="display:inline-block;padding:10px 14px;border-radius:10px;background:{{ $theme['primary'] ?? '#2563eb' }};color:#fff;text-decoration:none;font-weight:700;">
                        {{ $plan['cta'] ?? 'Elegir' }}
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>
