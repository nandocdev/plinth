<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $meta['title'] }}</title>
    @if (!empty($meta['description']))
        <meta name="description" content="{{ $meta['description'] }}" />
    @endif
</head>

<body
    style="margin:0;font-family:{{ $theme['font_stack'] ?? 'Instrument Sans,system-ui,-apple-system,sans-serif' }};background:{{ $theme['bg_page'] ?? '#f8fafc' }};color:{{ $theme['text_primary'] ?? '#0f172a' }};">
    @if (!$isPublished)
        <div style="background:#f59e0b;color:#111827;padding:10px 16px;font-weight:600;text-align:center;">
            Modo vista previa: esta landing está en borrador.
        </div>
    @endif

    @foreach ($blocks as $block)
        @includeIf('landing-builder::blocks.' . $block->block_type, [
            'settings' => is_array($block->settings) ? $block->settings : [],
            'theme' => $theme,
            'siteName' => $siteName,
        ])
    @endforeach

    @if (count($blocks) === 0)
        <main style="max-width:920px;margin:0 auto;padding:56px 20px;text-align:center;">
            <h1 style="font-size:44px;line-height:1.1;margin:0 0 18px;">{{ $content['headline'] }}</h1>
            <p
                style="font-size:18px;color:{{ $theme['text_secondary'] ?? '#475569' }};max-width:700px;margin:0 auto 28px;line-height:1.6;">
                {{ $content['description'] }}
            </p>
            <a href="{{ route('tenant.register', ['tenantDomain' => request()->route('tenantDomain')]) }}"
                style="display:inline-block;padding:12px 22px;border-radius:10px;background:{{ $theme['primary'] }};color:#fff;text-decoration:none;font-weight:700;">
                {{ $content['cta'] }}
            </a>
        </main>
    @endif
</body>

</html>
