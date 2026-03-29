<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Plinth Tenant') }} - SaaS Starter Kit Profesional</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                --brand-primary: #f53003;
                --brand-secondary: #FF4433;
            }
            .glass {
                background: rgba(255, 255, 255, 0.03);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.08);
            }
            .gradient-text {
                background: linear-gradient(to right, #ffffff, #a1a1aa);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .hero-gradient {
                background: radial-gradient(circle at 50% -20%, rgba(245, 48, 3, 0.15) 0%, rgba(10, 10, 10, 0) 50%);
            }
        </style>
    </head>
    <body class="antialiased bg-zinc-950 text-zinc-400 font-sans selection:bg-orange-500/30 selection:text-orange-200">
        
        <!-- Background Decoration -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10 hero-gradient"></div>

        <div class="relative min-h-screen flex flex-col">
            
            <!-- Navigation -->
            <header class="sticky top-0 z-50 w-full border-b border-white/5 bg-zinc-950/50 backdrop-blur-md">
                <div class="container mx-auto px-6 h-20 flex items-center justify-between">
                    <div class="flex items-center gap-8">
                        <a href="/" class="flex items-center gap-2 group">
                            <x-app-logo-icon class="w-8 h-8 text-orange-600 transition-transform group-hover:scale-110" />
                            <span class="text-white font-bold text-xl tracking-tight italic">Plinth</span>
                        </a>
                        
                        <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                            <a href="#features" class="hover:text-white transition-colors">Características</a>
                            <a href="#architecture" class="hover:text-white transition-colors">Arquitectura</a>
                            <a href="#pricing" class="hover:text-white transition-colors">Precios</a>
                            <a href="https://laravel.com/docs" target="_blank" class="hover:text-white transition-colors flex items-center gap-1">
                                Docs
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-external-link"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                            </a>
                        </nav>
                    </div>

                    <div class="flex items-center gap-4">
                        @if (Route::has('login'))
                            @auth
                                <flux:button href="{{ route('dashboard') }}" variant="subtle">Dashboard</flux:button>
                            @else
                                <flux:button href="{{ route('login') }}" variant="subtle">Iniciar sesión</flux:button>
                                @if (Route::has('register'))
                                    <flux:button href="{{ route('register') }}" variant="primary" color="orange" class="!hover:bg-orange-700 !border-none">Registrarse</flux:button>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </header>

            <main class="flex-grow">
                
                <!-- Hero Section -->
                <section class="relative pt-24 pb-20 md:pt-32 md:pb-32 overflow-hidden">
                    <div class="container mx-auto px-6 relative z-10 text-center">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-orange-500/20 bg-orange-500/5 text-orange-400 text-xs font-semibold mb-8 animate-fade-in">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                            </span>
                            Nueva Versión 1.0 disponible
                        </div>
                        
                        <h1 class="text-5xl md:text-7xl lg:text-8xl font-bold tracking-tight text-white mb-8 max-w-5xl mx-auto leading-[1.1]">
                            Construye tu próximo <span class="text-orange-600">SaaS</span> en tiempo récord.
                        </h1>
                        
                        <p class="text-lg md:text-xl text-zinc-400 max-w-2xl mx-auto mb-12 leading-relaxed">
                            El Starter Kit modular definitivo para Laravel 11. Multi-Tenancy nativo, Billing con Stripe y arquitectura profesional basada en Bounded Contexts.
                        </p>

                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-20">
                            <flux:button href="{{ route('register') }}" variant="primary" color="orange" class="!px-8 !py-4 !hover:bg-orange-700 !border-none !text-lg !font-semibold">
                                Comenzar ahora gratis
                            </flux:button>
                            <flux:button href="https://github.com" target="_blank" variant="subtle" class="!px-8 !py-4 !text-lg !font-semibold border border-white/10 hover:bg-white/5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 lucide lucide-github"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/></svg>
                                Ver en GitHub
                            </flux:button>
                        </div>

                        <!-- Hero Preview -->
                        <div class="relative max-w-6xl mx-auto">
                            <div class="absolute -inset-1 bg-gradient-to-r from-orange-600/20 to-violet-600/20 rounded-2xl blur-2xl opacity-50"></div>
                            <div class="relative rounded-xl border border-white/10 overflow-hidden shadow-2xl glass aspect-video md:aspect-[21/9] flex items-center justify-center">
                                <div class="p-8 md:p-12 w-full h-full flex flex-col justify-between text-left">
                                    <div class="flex items-center justify-between mb-8">
                                        <div class="flex gap-2">
                                            <div class="w-3 h-3 rounded-full bg-red-500/50"></div>
                                            <div class="w-3 h-3 rounded-full bg-yellow-500/50"></div>
                                            <div class="w-3 h-3 rounded-full bg-green-500/50"></div>
                                        </div>
                                        <div class="px-3 py-1 rounded bg-white/5 border border-white/5 text-[10px] uppercase tracking-widest font-bold text-white/40">
                                            Architecture Preview
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 flex-grow">
                                        <div class="p-6 rounded-lg border border-white/5 bg-white/5 flex flex-col gap-4">
                                            <div class="w-10 h-10 rounded bg-orange-600/20 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500 lucide lucide-layout-grid"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                                            </div>
                                            <div class="h-2 w-24 bg-white/20 rounded"></div>
                                            <div class="space-y-2">
                                                <div class="h-1.5 w-full bg-white/5 rounded"></div>
                                                <div class="h-1.5 w-4/5 bg-white/5 rounded"></div>
                                            </div>
                                        </div>
                                        <div class="p-6 rounded-lg border border-orange-500/20 bg-orange-500/5 flex flex-col gap-4 relative">
                                            <div class="absolute top-4 right-4 text-[10px] text-orange-500 font-bold">ACTIVE</div>
                                            <div class="w-10 h-10 rounded bg-orange-600 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white lucide lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                            </div>
                                            <div class="h-2 w-24 bg-white/40 rounded"></div>
                                            <div class="space-y-2">
                                                <div class="h-1.5 w-full bg-white/10 rounded"></div>
                                                <div class="h-1.5 w-4/5 bg-white/10 rounded"></div>
                                            </div>
                                        </div>
                                        <div class="p-6 rounded-lg border border-white/5 bg-white/5 flex flex-col gap-4">
                                            <div class="w-10 h-10 rounded bg-violet-600/20 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-500 lucide lucide-credit-card"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                            </div>
                                            <div class="h-2 w-24 bg-white/20 rounded"></div>
                                            <div class="space-y-2">
                                                <div class="h-1.5 w-full bg-white/5 rounded"></div>
                                                <div class="h-1.5 w-4/5 bg-white/5 rounded"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-8 pt-8 border-t border-white/5 flex items-center justify-between text-xs font-mono text-zinc-500">
                                        <div>tenant.active_domain: acme.plinth.test</div>
                                        <div class="flex items-center gap-4">
                                            <span class="flex items-center gap-1 text-green-500/80"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> PostgreSQL Connected</span>
                                            <span>v1.0.0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Features Section -->
                <section id="features" class="py-24 bg-zinc-950/50 relative">
                    <div class="container mx-auto px-6">
                        <div class="text-center mb-20">
                            <h2 class="text-orange-500 font-bold tracking-widest text-xs uppercase mb-4">Potencia sin límites</h2>
                            <h3 class="text-3xl md:text-5xl font-bold text-white mb-6">Todo lo que necesitas para escalar</h3>
                            <p class="text-zinc-400 max-w-2xl mx-auto">Hemos empaquetado años de experiencia en arquitectura SaaS en una base de código limpia y modular.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            <!-- Feature 1 -->
                            <div class="p-8 rounded-2xl border border-white/5 bg-white/5 hover:border-orange-500/20 transition-all group">
                                <div class="w-12 h-12 rounded-xl bg-orange-600/10 border border-orange-600/20 flex items-center justify-center mb-6 group-hover:bg-orange-600 group-hover:text-white transition-all text-orange-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-database"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/></svg>
                                </div>
                                <h4 class="text-xl font-semibold text-white mb-4">Multi-Tenancy Real</h4>
                                <p class="text-zinc-400 text-sm leading-relaxed">Aislamiento total por base de datos PostgreSQL. Soporte nativo para subdominios y dominios personalizados.</p>
                            </div>

                            <!-- Feature 2 -->
                            <div class="p-8 rounded-2xl border border-white/5 bg-white/5 hover:border-orange-500/20 transition-all group">
                                <div class="w-12 h-12 rounded-xl bg-violet-600/10 border border-violet-600/20 flex items-center justify-center mb-6 group-hover:bg-violet-600 group-hover:text-white transition-all text-violet-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-box"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                </div>
                                <h4 class="text-xl font-semibold text-white mb-4">Arquitectura Modular</h4>
                                <p class="text-zinc-400 text-sm leading-relaxed">Basado en Bounded Contexts. Separa la lógica Central de la del Tenant sin complicaciones.</p>
                            </div>

                            <!-- Feature 3 -->
                            <div class="p-8 rounded-2xl border border-white/5 bg-white/5 hover:border-orange-500/20 transition-all group">
                                <div class="w-12 h-12 rounded-xl bg-blue-600/10 border border-blue-600/20 flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-all text-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                                </div>
                                <h4 class="text-xl font-semibold text-white mb-4">Billing Integrado</h4>
                                <p class="text-zinc-400 text-sm leading-relaxed">Laravel Cashier + Stripe preconfigurados. Gestión de planes, suscripciones y webhooks lista.</p>
                            </div>

                            <!-- Feature 4 -->
                            <div class="p-8 rounded-2xl border border-white/5 bg-white/5 hover:border-orange-500/20 transition-all group">
                                <div class="w-12 h-12 rounded-xl bg-emerald-600/10 border border-emerald-600/20 flex items-center justify-center mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-all text-emerald-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-check"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                                </div>
                                <h4 class="text-xl font-semibold text-white mb-4">Seguridad y Roles</h4>
                                <p class="text-zinc-400 text-sm leading-relaxed">Spatie Laravel Permission con soporte para equipos. Control granular de acceso por tenant.</p>
                            </div>

                            <!-- Feature 5 -->
                            <div class="p-8 rounded-2xl border border-white/5 bg-white/5 hover:border-orange-500/20 transition-all group">
                                <div class="w-12 h-12 rounded-xl bg-rose-600/10 border border-rose-600/20 flex items-center justify-center mb-6 group-hover:bg-rose-600 group-hover:text-white transition-all text-rose-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-command"><path d="M15 6a3 3 0 1 0-6 0 3 3 0 0 0 6 0Zm0 12a3 3 0 1 0-6 0 3 3 0 0 0 6 0ZM6 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm12 0a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z"/></svg>
                                </div>
                                <h4 class="text-xl font-semibold text-white mb-4">Dev Experience</h4>
                                <p class="text-zinc-400 text-sm leading-relaxed">Livewire, Flux UI y Tailwind CSS. Una experiencia de desarrollo moderna y productiva.</p>
                            </div>

                            <!-- Feature 6 -->
                            <div class="p-8 rounded-2xl border border-white/5 bg-white/5 hover:border-orange-500/20 transition-all group">
                                <div class="w-12 h-12 rounded-xl bg-cyan-600/10 border border-cyan-600/20 flex items-center justify-center mb-6 group-hover:bg-cyan-600 group-hover:text-white transition-all text-cyan-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-puzzle"><path d="M12 8a.5.5 0 0 1 .5.5v1.5a.5.5 0 0 0 .5.5h1.5a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1.5a.5.5 0 0 0-.5.5v1.5a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1.5a.5.5 0 0 0-.5-.5h-1.5a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 0 .5-.5V8.5a.5.5 0 0 1 .5-.5z"/><path d="M10 2H8a2 2 0 0 0-2 2v2"/><path d="M2 8v2a2 2 0 0 0 2 2h2"/><path d="M14 2h2a2 2 0 0 1 2 2v2"/><path d="M22 8v2a2 2 0 0 1-2 2h-2"/><path d="M10 22H8a2 2 0 0 1-2-2v-2"/><path d="M2 14v2a2 2 0 0 1 2 2h2"/><path d="M14 22h2a2 2 0 0 0 2-2v-2"/><path d="M22 14v2a2 2 0 0 0-2 2h-2"/></svg>
                                </div>
                                <h4 class="text-xl font-semibold text-white mb-4">Feature Flags</h4>
                                <p class="text-zinc-400 text-sm leading-relaxed">Controla el acceso a funcionalidades por plan o por cliente usando Laravel Pennant.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Architecture Section -->
                <section id="architecture" class="py-24 overflow-hidden">
                    <div class="container mx-auto px-6">
                        <div class="flex flex-col lg:flex-row items-center gap-16">
                            <div class="flex-1">
                                <h2 class="text-orange-500 font-bold tracking-widest text-xs uppercase mb-4">Diseñado para la mantenibilidad</h2>
                                <h3 class="text-4xl md:text-5xl font-bold text-white mb-8">Arquitectura Modular Profesional</h3>
                                <p class="text-lg text-zinc-400 mb-8 leading-relaxed">
                                    No más código espagueti. Separamos tu aplicación en tres contextos claros que garantizan la escalabilidad a largo plazo.
                                </p>
                                
                                <div class="space-y-6">
                                    <div class="flex gap-4">
                                        <div class="shrink-0 w-6 h-6 rounded-full bg-orange-600 flex items-center justify-center text-white text-xs font-bold">1</div>
                                        <div>
                                            <h5 class="text-white font-semibold mb-1">Central Context</h5>
                                            <p class="text-sm">Gestión de la plataforma, registro de usuarios, billing global y administración del sistema.</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-4">
                                        <div class="shrink-0 w-6 h-6 rounded-full bg-orange-600 flex items-center justify-center text-white text-xs font-bold">2</div>
                                        <div>
                                            <h5 class="text-white font-semibold mb-1">Tenant Context</h5>
                                            <p class="text-sm">El "espacio de trabajo" aislado para tus clientes. Totalmente separado a nivel lógico y de datos.</p>
                                        </div>
                                    </div>
                                    <div class="flex gap-4">
                                        <div class="shrink-0 w-6 h-6 rounded-full bg-orange-600 flex items-center justify-center text-white text-xs font-bold">3</div>
                                        <div>
                                            <h5 class="text-white font-semibold mb-1">Shared Context</h5>
                                            <p class="text-sm">Contratos, DTOs y utilidades compartidas entre ambos contextos de forma segura.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-1 relative">
                                <div class="absolute -inset-4 bg-orange-600/10 blur-3xl rounded-full opacity-50"></div>
                                <div class="relative glass rounded-2xl border border-white/10 p-4 transform rotate-2">
                                    <pre class="text-[10px] md:text-xs font-mono leading-tight text-zinc-500">
<span class="text-orange-500">app/</span>
├── <span class="text-white">Central/</span>
│   ├── BillingModule/
│   ├── TenantProvisioningModule/
│   └── AuthenticationModule/
├── <span class="text-white">Tenant/</span>
│   ├── WorkspaceModule/
│   ├── FeatureFlagsModule/
│   └── AuthorizationModule/
└── <span class="text-white">Shared/</span>
    ├── DTOs/
    ├── Contracts/
    └── Support/
                                    </pre>
                                </div>
                                <div class="absolute -bottom-8 -left-8 glass rounded-2xl border border-white/10 p-6 hidden md:block max-w-xs animate-bounce-slow">
                                    <p class="text-xs italic text-zinc-400 tracking-tight leading-relaxed">
                                        "La mejor arquitectura para SaaS que he usado en Laravel. La separación de contextos es simplemente brillante."
                                    </p>
                                    <div class="mt-4 flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-orange-600/20 border border-orange-600/20 flex items-center justify-center text-orange-500 font-bold text-[10px]">FC</div>
                                        <span class="text-[10px] text-white font-bold">Fernando Castillo - Lead Architect</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Pricing Section -->
                <section id="pricing" class="py-24 bg-zinc-950/50">
                    <div class="container mx-auto px-6">
                        <div class="text-center mb-20">
                            <h2 class="text-orange-500 font-bold tracking-widest text-xs uppercase mb-4">Planes flexibles</h2>
                            <h3 class="text-3xl md:text-5xl font-bold text-white mb-6">Precios que crecen contigo</h3>
                            <p class="text-zinc-400 max-w-2xl mx-auto">Sin sorpresas. Empieza gratis y escala a medida que tu negocio lo necesite.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                            <!-- Starter -->
                            <div class="p-10 rounded-3xl border border-white/5 bg-white/5 flex flex-col items-center">
                                <h4 class="text-zinc-400 font-bold uppercase tracking-[0.2em] text-[10px] mb-4">Starter</h4>
                                <div class="text-4xl font-bold text-white mb-2">$9<span class="text-lg font-normal text-zinc-500">/mes</span></div>
                                <p class="text-xs text-zinc-500 mb-8">Ideal para pequeños proyectos.</p>
                                <ul class="w-full space-y-4 mb-10">
                                    <li class="flex items-center gap-3 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500 lucide lucide-check"><path d="M20 6 9 17l-5-5"/></svg> 5 miembros</li>
                                    <li class="flex items-center gap-3 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500 lucide lucide-check"><path d="M20 6 9 17l-5-5"/></svg> 14 días trial</li>
                                    <li class="flex items-center gap-3 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500 lucide lucide-check"><path d="M20 6 9 17l-5-5"/></svg> Soporte por email</li>
                                    <li class="flex items-center gap-3 text-sm text-zinc-600"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg> Dominio personalizado</li>
                                </ul>
                                <flux:button href="{{ route('register') }}" variant="subtle" class="w-full border-white/10 hover:bg-white/5">Comenzar Starter</flux:button>
                            </div>

                            <!-- Pro -->
                            <div class="p-10 rounded-3xl border-2 border-orange-600 bg-orange-600/5 flex flex-col items-center relative scale-105 shadow-2xl">
                                <div class="absolute -top-4 px-4 py-1 bg-orange-600 text-white text-[10px] font-bold uppercase tracking-widest rounded-full">Más Popular</div>
                                <h4 class="text-orange-500 font-bold uppercase tracking-[0.2em] text-[10px] mb-4">Pro</h4>
                                <div class="text-4xl font-bold text-white mb-2">$29<span class="text-lg font-normal text-zinc-500">/mes</span></div>
                                <p class="text-xs text-zinc-500 mb-8">Para equipos en crecimiento.</p>
                                <ul class="w-full space-y-4 mb-10">
                                    <li class="flex items-center gap-3 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500 lucide lucide-check"><path d="M20 6 9 17l-5-5"/></svg> 20 miembros</li>
                                    <li class="flex items-center gap-3 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500 lucide lucide-check"><path d="M20 6 9 17l-5-5"/></svg> 14 días trial</li>
                                    <li class="flex items-center gap-3 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500 lucide lucide-check"><path d="M20 6 9 17l-5-5"/></svg> Soporte prioritario</li>
                                    <li class="flex items-center gap-3 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500 lucide lucide-check"><path d="M20 6 9 17l-5-5"/></svg> Dominio personalizado</li>
                                </ul>
                                <flux:button href="{{ route('register') }}" variant="primary" color="orange" class="w-full !hover:bg-orange-700 !border-none">Elegir Plan Pro</flux:button>
                            </div>

                            <!-- Enterprise -->
                            <div class="p-10 rounded-3xl border border-white/5 bg-white/5 flex flex-col items-center">
                                <h4 class="text-zinc-400 font-bold uppercase tracking-[0.2em] text-[10px] mb-4">Enterprise</h4>
                                <div class="text-4xl font-bold text-white mb-2">$99<span class="text-lg font-normal text-zinc-500">/mes</span></div>
                                <p class="text-xs text-zinc-500 mb-8">Control total para empresas.</p>
                                <ul class="w-full space-y-4 mb-10">
                                    <li class="flex items-center gap-3 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500 lucide lucide-check"><path d="M20 6 9 17l-5-5"/></svg> Miembros ilimitados</li>
                                    <li class="flex items-center gap-3 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500 lucide lucide-check"><path d="M20 6 9 17l-5-5"/></svg> SLA garantizado</li>
                                    <li class="flex items-center gap-3 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500 lucide lucide-check"><path d="M20 6 9 17l-5-5"/></svg> Onboarding dedicado</li>
                                    <li class="flex items-center gap-3 text-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500 lucide lucide-check"><path d="M20 6 9 17l-5-5"/></svg> Soporte 24/7</li>
                                </ul>
                                <flux:button href="{{ route('register') }}" variant="subtle" class="w-full border-white/10 hover:bg-white/5">Hablar con ventas</flux:button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Final CTA -->
                <section class="py-24 relative overflow-hidden">
                    <div class="container mx-auto px-6 text-center relative z-10">
                        <h2 class="text-4xl md:text-6xl font-bold text-white mb-8">¿Listo para lanzar tu SaaS?</h2>
                        <p class="text-xl text-zinc-400 max-w-2xl mx-auto mb-12">Únete a cientos de desarrolladores que ya están construyendo con Plinth.</p>
                        <flux:button href="{{ route('register') }}" variant="primary" color="orange" class="!px-12 !py-6 !hover:bg-orange-700 !border-none !text-xl !font-bold">
                            Empezar ahora mismo
                        </flux:button>
                    </div>
                </section>
            </main>

            <!-- Footer -->
            <footer class="border-t border-white/5 py-12 bg-black">
                <div class="container mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="flex items-center gap-2">
                        <x-app-logo-icon class="w-6 h-6 text-orange-600" />
                        <span class="text-white font-bold tracking-tight italic">Plinth</span>
                    </div>
                    
                    <div class="flex items-center gap-8 text-sm">
                        <a href="#" class="hover:text-white transition-colors">Twitter</a>
                        <a href="#" class="hover:text-white transition-colors">GitHub</a>
                        <a href="#" class="hover:text-white transition-colors">Discord</a>
                    </div>

                    <div class="text-xs text-zinc-600">
                        © 2026 Plinth Tenant. Todos los derechos reservados.
                    </div>
                </div>
            </footer>
        </div>

        <script>
            // Simple animation on scroll
            document.addEventListener('DOMContentLoaded', function() {
                const observerOptions = {
                    threshold: 0.1
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animate-in');
                            entry.target.classList.remove('opacity-0');
                        }
                    });
                }, observerOptions);

                document.querySelectorAll('section').forEach(section => {
                    section.classList.add('transition-all', 'duration-1000', 'opacity-0');
                    observer.observe(section);
                });
                
                // Hero is always visible
                const hero = document.querySelector('section');
                hero.classList.remove('opacity-0');
            });
        </script>
    </body>
</html>
