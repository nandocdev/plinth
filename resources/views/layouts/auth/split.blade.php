<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
                <div class="absolute inset-0 bg-neutral-900"></div>
                <a href="{{ route('home') }}" class="relative z-20 flex items-center text-lg font-medium" wire:navigate>
                    <span class="flex h-10 w-10 items-center justify-center rounded-md">
                        <x-app-logo-icon class="me-2 h-7 fill-current text-white" />
                    </span>
                    {{ config('app.name', 'Laravel') }}
                </a>

                <div class="relative z-20 mt-auto">
                    <div class="p-8 rounded-3xl border border-white/10 bg-white/5 backdrop-blur-2xl shadow-2xl overflow-hidden group">
                        <!-- Decoración de Luz Indigo -->
                        <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-600/20 blur-3xl rounded-full group-hover:bg-indigo-600/30 transition-colors duration-700"></div>
                        
                        <div class="relative space-y-6">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-indigo-500/20 bg-indigo-500/10 text-indigo-400 text-[10px] font-bold uppercase tracking-widest">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                                </span>
                                Central Management v1.0
                            </div>

                            <h2 class="text-3xl font-bold text-white leading-tight">
                                Control Total de tu <br>
                                <span class="text-indigo-500">Ecosistema SaaS</span>.
                            </h2>

                            <p class="text-zinc-400 text-sm leading-relaxed max-w-sm">
                                Gestiona provisionamiento, suscripciones y salud del sistema desde una interfaz centralizada y segura.
                            </p>

                            <!-- Status Pills -->
                            <div class="flex flex-wrap gap-3 pt-4 border-t border-white/5">
                                <div class="flex items-center gap-2 text-[10px] text-zinc-500 font-mono">
                                    <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                    DB Cluster: Healthy
                                </div>
                                <div class="flex items-center gap-2 text-[10px] text-zinc-500 font-mono">
                                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-500"></div>
                                    Redis Cache: Active
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex h-9 w-9 items-center justify-center rounded-md">
                            <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                        </span>

                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
