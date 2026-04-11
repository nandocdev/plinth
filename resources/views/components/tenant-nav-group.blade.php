@props(['item'])

@php
    $isActive = $item['active'] ?? false;
@endphp

<div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }">
    <button type="button" @click="open = !open"
        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
               {{ $isActive
                   ? 'text-zinc-900 dark:text-zinc-100'
                   : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:text-white' }}">
        <flux:icon :name="$item['icon']" class="size-4 shrink-0" />
        <span class="flex-1 text-start">{{ $item['label'] }}</span>
        <flux:icon name="chevron-down" class="size-3 transition-transform duration-200"
            ::class="{ 'rotate-180': open }" />
    </button>

    <div x-show="open" x-collapse class="ml-7 mt-1 space-y-0.5 border-l border-zinc-200 pl-3 dark:border-zinc-700">
        @foreach ($item['children'] as $child)
            <a href="{{ route($child['route']) }}" wire:navigate
                class="block rounded-md px-3 py-1.5 text-xs font-medium transition
                       {{ $child['active'] ?? false
                           ? 'text-zinc-900 dark:text-white'
                           : 'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}">
                {{ $child['label'] }}
            </a>
        @endforeach
    </div>
</div>
