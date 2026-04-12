@php $milestones = is_array($settings['milestones'] ?? null) ? $settings['milestones'] : []; @endphp

<section style="padding:52px 20px;background:{{ $theme['bg_section'] ?? '#ffffff' }};">
    <div style="max-width:960px;margin:0 auto;">
        <h2 style="font-size:30px;margin:0 0 18px;color:{{ $theme['text_primary'] ?? '#111827' }};">
            {{ $settings['title'] ?? 'Historia' }}</h2>
        <div style="display:grid;gap:10px;">
            @foreach ($milestones as $milestone)
                <article
                    style="border-left:3px solid {{ $theme['primary'] ?? '#2563eb' }};padding:8px 12px;background:{{ $theme['bg_card'] ?? '#ffffff' }};border-radius:8px;">
                    <p style="margin:0 0 4px;font-size:13px;font-weight:700;color:{{ $theme['primary'] ?? '#2563eb' }};">
                        {{ $milestone['year'] ?? '' }}</p>
                    <p style="margin:0;color:{{ $theme['text_secondary'] ?? '#475569' }};">
                        {{ $milestone['event'] ?? '' }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
