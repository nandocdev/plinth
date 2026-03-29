<x-layouts::app :title="__('Tenant Onboarding Wizard')">
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="xl">{{ __('Tenant onboarding wizard') }}</flux:heading>
            <flux:subheading>
                {{ __('Create tenant, default domain and assign a billing plan in one flow.') }}
            </flux:subheading>

            @if (session('status'))
                <flux:text class="mt-4 text-green-600 dark:text-green-400">{{ session('status') }}</flux:text>
            @endif

            <form wire:submit="onboardTenant" class="mt-6 grid gap-4 md:grid-cols-2">
                <flux:input wire:model="form.name" :label="__('Tenant name')" :placeholder="__('Acme Inc')" required />

                <flux:input wire:model="form.primaryDomain" :label="__('Default domain')"
                    :placeholder="__('acme.localhost')" required />

                <flux:input wire:model="form.brandName" :label="__('Brand name (optional)')"
                    :placeholder="__('Acme Workspace')" />

                <flux:input wire:model="form.logoUrl" :label="__('Logo URL (optional)')"
                    :placeholder="__('https://cdn.example.com/logo.svg')" />

                <div>
                    <label for="onboarding-region" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                        {{ __('Region') }}
                    </label>
                    <select id="onboarding-region" wire:model="form.region"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                        required>
                        @foreach ($regionOptions as $region)
                            <option value="{{ $region->code }}">{{ $region->label }} ({{ $region->code }})</option>
                        @endforeach
                    </select>
                    @error('form.region')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="onboarding-plan" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                        {{ __('Plan') }}
                    </label>
                    <select id="onboarding-plan" wire:model="form.planId"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                        required>
                        <option value="">{{ __('Select a plan') }}</option>
                        @foreach ($planOptions as $plan)
                            <option value="{{ $plan['id'] }}">{{ $plan['name'] }}</option>
                        @endforeach
                    </select>
                    @error('form.planId')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="onboarding-period" class="mb-1 block text-sm text-zinc-700 dark:text-zinc-300">
                        {{ __('Billing period') }}
                    </label>
                    <select id="onboarding-period" wire:model="form.billingPeriod"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                        required>
                        <option value="monthly">{{ __('Monthly') }}</option>
                        <option value="yearly">{{ __('Yearly') }}</option>
                    </select>
                    @error('form.billingPeriod')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <flux:input wire:model="form.primaryColor" :label="__('Primary color')" :placeholder="__('#f53003')" />

                <flux:input wire:model="form.secondaryColor" :label="__('Secondary color')"
                    :placeholder="__('#ff4433')" />

                <flux:input wire:model="form.referralCode" :label="__('Referral code (optional)')"
                    :placeholder="__('PARTNER10')" class="md:col-span-2" />

                <div class="md:col-span-2 flex items-center gap-3">
                    <flux:button type="submit" variant="primary">
                        {{ __('Complete onboarding') }}
                    </flux:button>

                    <flux:button :href="route('central.tenants.index')" wire:navigate variant="filled">
                        {{ __('Back to tenants') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
