@php $images = is_array($settings['images'] ?? null) ? $settings['images'] : []; @endphp

<section style="padding:48px 20px;background:{{ $theme['bg_page'] ?? '#f8fafc' }};">
    <div style="max-width:1100px;margin:0 auto;">
        <h2 style="font-size:30px;margin:0 0 22px;color:{{ $theme['text_primary'] ?? '#111827' }};">
            {{ $settings['title'] ?? 'Galería' }}</h2>
        <div style="display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
            @foreach ($images as $image)
                <figure
                    style="margin:0;border-radius:14px;overflow:hidden;border:1px solid {{ $theme['neutral'] ?? '#e5e7eb' }};background:{{ $theme['bg_card'] ?? '#ffffff' }};">
                    <img src="{{ $image['url'] ?? '' }}" alt="{{ $image['alt'] ?? 'Imagen' }}"
                        style="width:100%;height:190px;object-fit:cover;display:block;" />
                </figure>
            @endforeach
        </div>
    </div>
</section>
