@props([
    'steps' => [],
    'currentStep' => 1,
    'progressPercent' => 0,
])

<div class="space-y-3">
    <div class="flex items-center justify-between">
        <flux:text class="text-xs uppercase tracking-widest text-zinc-500">Asistente de Registro</flux:text>
        <flux:badge color="zinc" size="sm">Paso {{ $currentStep }} de {{ count($steps) }}</flux:badge>
    </div>

    <div class="h-2 rounded-full bg-zinc-800/80 overflow-hidden">
        <div class="h-full rounded-full bg-gradient-to-r from-orange-500 to-amber-400 transition-all duration-300"
            style="width: {{ $progressPercent }}%"></div>
    </div>

    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        @foreach ($steps as $step)
            @php
                $number = (int) $step['number'];
                $isDone = $number < $currentStep;
                $isCurrent = $number === $currentStep;
            @endphp

            <button type="button" wire:click="goToStep({{ $number }})"
                class="rounded-xl border p-3 text-left transition
                {{ $isCurrent ? 'border-orange-500 bg-orange-500/10' : 'border-zinc-700 bg-zinc-900/50 hover:border-zinc-500' }}">
                <div class="flex items-center gap-2">
                    @if ($isDone)
                        <flux:icon name="check-circle" class="size-4 text-green-400" />
                    @else
                        <flux:icon :name="$step['icon']"
                            class="size-4 {{ $isCurrent ? 'text-orange-400' : 'text-zinc-500' }}" />
                    @endif
                    <span class="text-xs font-semibold {{ $isCurrent ? 'text-orange-300' : 'text-zinc-300' }}">
                        {{ $step['label'] }}
                    </span>
                </div>
                <p class="mt-1 text-[11px] {{ $isCurrent ? 'text-orange-200/80' : 'text-zinc-500' }}">
                    {{ $step['description'] }}
                </p>
            </button>
        @endforeach
    </div>
</div>
