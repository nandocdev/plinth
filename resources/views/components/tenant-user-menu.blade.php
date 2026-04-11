@props(['variant' => 'sidebar'])

@php
    /** @var \App\Tenant\AuthenticationModule\Models\User|null $user */
    $user = auth('tenant')->user();

    if (!$user) {
        return;
    }

    $name = $user->name ?? '';
    $email = $user->email ?? '';
    $initials = collect(explode(' ', $name))
        ->take(2)
        ->map(fn($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp

<flux:dropdown position="{{ $variant === 'mobile' ? 'top' : 'bottom' }}"
    align="{{ $variant === 'mobile' ? 'end' : 'start' }}">

    @if ($variant === 'mobile')
        <flux:profile :initials="$initials" icon-trailing="chevron-down" />
    @else
        <flux:sidebar.profile :name="$name" :initials="$initials" icon:trailing="chevrons-up-down" />
    @endif

    <flux:menu>
        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
            <flux:avatar :name="$name" :initials="$initials" />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">{{ $name }}</flux:heading>
                <flux:text class="truncate">{{ $email }}</flux:text>
            </div>
        </div>

        <flux:menu.separator />

        <flux:menu.radio.group>
            <flux:menu.item :href="route('tenant.profile')" icon="cog" wire:navigate>
                {{ __('Perfil') }}
            </flux:menu.item>
        </flux:menu.radio.group>

        <flux:menu.separator />

        <form method="POST" action="{{ url('/logout') }}" class="w-full">
            @csrf
            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                class="w-full cursor-pointer">
                {{ __('Salir') }}
            </flux:menu.item>
        </form>
    </flux:menu>

</flux:dropdown>
