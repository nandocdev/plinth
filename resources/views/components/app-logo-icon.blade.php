<svg {{ $attributes }} width="100" height="88" viewBox="0 0 100 88" fill="none" version="1.1" id="svg15"
    xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg">
    <defs id="defs5">
        <linearGradient id="plinthGradient" x1="40" y1="20" x2="140" y2="140"
            gradientUnits="userSpaceOnUse">
            <stop stop-color="#6366f1" id="stop1" />
            <stop offset="1" stop-color="#4f46e5" id="stop2" />
        </linearGradient>
        <linearGradient id="accentGradient" x1="15.132547" y1="10.991113" x2="85.072357" y2="80.930923"
            gradientTransform="scale(0.99914943,1.0008513)" gradientUnits="userSpaceOnUse">
            <stop stop-color="#818cf8" id="stop3" />
            <stop offset="1" stop-color="#6366f1" id="stop4" />
        </linearGradient>
        <filter id="softGlow" x="-0.72" y="-0.72" width="2.44" height="2.44">
            <feGaussianBlur stdDeviation="6" result="blur" id="feGaussianBlur4" />
            <feMerge id="feMerge5">
                <feMergeNode in="blur" id="feMergeNode4" />
                <feMergeNode in="SourceGraphic" id="feMergeNode5" />
            </feMerge>
        </filter>
        <linearGradient xlink:href="#plinthGradient" id="linearGradient2" gradientUnits="userSpaceOnUse" x1="40"
            y1="20" x2="140" y2="140" />
        <linearGradient xlink:href="#plinthGradient" id="linearGradient3" gradientUnits="userSpaceOnUse" x1="40"
            y1="20" x2="140" y2="140" />
    </defs>
    <!-- Background -->
    <rect width="640" height="180" rx="24" fill="#0f172a" id="rect5" x="-40" y="-30"
        style="display:none" />
    <!-- Symbol -->
    <g id="g14">
        <!-- Base platform -->
        <rect x="0" y="70" width="100" height="18" rx="9" fill="url(#plinthGradient)" id="rect6"
            style="display:inline;fill:url(#linearGradient2)" />
        <!-- Left pillar -->
        <rect x="14" y="18" width="18" height="58" rx="9" fill="#e2e8f0" id="rect7" />
        <!-- Right pillar -->
        <rect x="68" y="18" width="18" height="58" rx="9" fill="#e2e8f0" id="rect8" />
        <!-- Top beam -->
        <rect x="6" y="0" width="88" height="18" rx="9" fill="url(#plinthGradient)" id="rect9"
            style="fill:url(#linearGradient3)" />
        <!-- Center core -->
        <circle cx="50" cy="46" r="10" fill="#22d3ee" filter="url(#softGlow)" id="circle9" />
        <!-- Tenant nodes -->
        <circle cx="50" cy="46" r="34" stroke="url(#accentGradient)" stroke-width="2"
            stroke-dasharray="4, 6" id="circle10" style="stroke:url(#accentGradient)" />
        <circle cx="50" cy="12" r="4" fill="#a78bfa" id="circle11" />
        <circle cx="84" cy="46" r="4" fill="#22d3ee" id="circle12" />
        <circle cx="50" cy="80" r="4" fill="#a78bfa" id="circle13" />
        <circle cx="16" cy="46" r="4" fill="#22d3ee" id="circle14" />
    </g>
    <!-- Wordmark -->
</svg>
