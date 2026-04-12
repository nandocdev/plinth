@php
    $bgColor = $settings['navbar_bg_color'] ?? '#ffffff';
    $textColor = $settings['navbar_text_color'] ?? '#0f172a';
    $linkColor = $settings['navbar_link_color'] ?? '#2563eb';
    $layoutStyle = $settings['layout_style'] ?? 'normal';
    $logoUrl = $settings['logo_url'] ?? '';
    $hasLogo = !empty($logoUrl);

    $maxWidth = match ($layoutStyle) {
        'compact' => '56rem',
        'wide' => '100%',
        default => '64rem',
    };

    $padding = match ($layoutStyle) {
        'compact' => '12px 16px',
        default => '16px 20px',
    };
@endphp

<header
    style="position:sticky;top:0;z-index:40;background:{{ $bgColor }};backdrop-filter:blur(8px);border-bottom:1px solid color-mix(in srgb, {{ $textColor }} 12%, transparent);box-shadow:0 1px 3px rgba(0,0,0,0.08);">
    <div
        style="max-width:{{ $maxWidth }};margin:0 auto;padding:{{ $padding }};display:flex;align-items:center;justify-content:space-between;gap:16px;">
        <a href="#top"
            style="display:flex;align-items:center;gap:10px;text-decoration:none;color:{{ $textColor }};flex-shrink:0;">
            @if ($hasLogo)
                <img src="{{ $logoUrl }}" alt="Logo" style="height:32px;width:auto;max-width:40px;" />
            @endif
            <span style="font-weight:800;letter-spacing:0.02em;font-size:16px;white-space:nowrap;">
                {{ $settings['brand_label'] ?? $siteName }}
            </span>
        </a>

        <nav aria-label="Navegación principal"
            style="display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px;align-items:center;">
            @foreach ($menuSections as $item)
                <a href="{{ $item['href'] }}"
                    style="padding:6px 12px;border-radius:6px;font-size:13px;font-weight:600;color:{{ $linkColor }};text-decoration:none;transition:all 0.2s ease;cursor:pointer;">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
