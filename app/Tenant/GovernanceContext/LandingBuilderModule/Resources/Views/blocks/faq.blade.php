@php $items = is_array($settings['items'] ?? null) ? $settings['items'] : []; @endphp

<section style="padding:52px 20px;background:{{ $theme['bg_page'] ?? '#f8fafc' }};">
    <div style="max-width:900px;margin:0 auto;">
        <h2 style="font-size:30px;margin:0 0 20px;color:{{ $theme['text_primary'] ?? '#111827' }};">
            {{ $settings['title'] ?? 'Preguntas frecuentes' }}</h2>
        <div style="display:grid;gap:10px;">
            @foreach ($items as $item)
                <article
                    style="border:1px solid {{ $theme['neutral'] ?? '#e5e7eb' }};border-radius:12px;padding:14px;background:{{ $theme['bg_card'] ?? '#ffffff' }};">
                    <h3 style="margin:0 0 6px;font-size:16px;color:{{ $theme['text_primary'] ?? '#0f172a' }};">
                        {{ $item['question'] ?? '' }}</h3>
                    <p style="margin:0;color:{{ $theme['text_secondary'] ?? '#475569' }};line-height:1.6;">
                        {{ $item['answer'] ?? '' }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
