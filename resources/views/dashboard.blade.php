<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-6">
        <!-- Welcoming Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <flux:heading size="xl" class="font-extrabold tracking-tight">{{ __('Welcome back,') }} {{ auth()->user()->name }}</flux:heading>
                <flux:text class="mt-1">{{ __('Manage your members and issue digital wallet passes for BNT.') }}</flux:text>
            </div>
            <div>
                <flux:button href="{{ route('members.index') }}" variant="primary" icon="users" wire:navigate>
                    {{ __('Manage Members') }}
                </flux:button>
            </div>
        </div>

        <flux:separator />

        <!-- Metrics Grid -->
        <div class="grid gap-6 md:grid-cols-3">
            <!-- Active Members -->
            <flux:card class="p-6 flex flex-col justify-between border-emerald-500/20 dark:border-emerald-500/10">
                <div class="flex justify-between items-start">
                    <div>
                        <flux:text class="text-xs uppercase font-bold tracking-wider text-emerald-600 dark:text-emerald-400">{{ __('Active Members') }}</flux:text>
                        <flux:heading size="xxl" class="mt-2 font-black tracking-tight">{{ $totalMembers }}</flux:heading>
                    </div>
                    <span class="text-3xl p-2 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 rounded-lg">👥</span>
                </div>
                <flux:text class="text-xs mt-4 text-zinc-500">{{ __('Total registered active memberships') }}</flux:text>
            </flux:card>

            <!-- Cards Delivered -->
            <flux:card class="p-6 flex flex-col justify-between border-amber-500/20 dark:border-amber-500/10">
                <div class="flex justify-between items-start">
                    <div>
                        <flux:text class="text-xs uppercase font-bold tracking-wider text-amber-600 dark:text-amber-400">{{ __('Cards Delivered') }}</flux:text>
                        <flux:heading size="xxl" class="mt-2 font-black tracking-tight">{{ $cardsSent }}</flux:heading>
                    </div>
                    <span class="text-3xl p-2 bg-amber-50 dark:bg-amber-950/30 text-amber-600 rounded-lg">✉️</span>
                </div>
                <flux:text class="text-xs mt-4 text-zinc-500">{{ __('Cards distributed to members via email') }}</flux:text>
            </flux:card>

            <!-- Passes Activated -->
            <flux:card class="p-6 flex flex-col justify-between border-sky-500/20 dark:border-sky-500/10">
                <div class="flex justify-between items-start">
                    <div>
                        <flux:text class="text-xs uppercase font-bold tracking-wider text-sky-600 dark:text-sky-400">{{ __('Passes Activated') }}</flux:text>
                        <flux:heading size="xxl" class="mt-2 font-black tracking-tight">{{ $passesDownloaded }}</flux:heading>
                    </div>
                    <span class="text-3xl p-2 bg-sky-50 dark:bg-sky-950/30 text-sky-600 rounded-lg">📲</span>
                </div>
                <flux:text class="text-xs mt-4 text-zinc-500">{{ __('Wallet passes installed on user devices') }}</flux:text>
            </flux:card>
        </div>

        <!-- Banner Call to Action -->
        <flux:card class="relative overflow-hidden p-8 flex flex-col md:flex-row items-center justify-between gap-6 border-zinc-200 dark:border-zinc-800 bg-gradient-to-r from-emerald-500/10 to-teal-500/10">
            <div class="flex-1">
                <flux:heading class="font-bold tracking-tight text-emerald-800 dark:text-emerald-300">{{ __('Ready to enroll a new member?') }}</flux:heading>
                <flux:text class="mt-2 text-sm leading-relaxed max-w-xl">
                    {{ __('Enroll members into BNT and automatically issue their digital cards. Members can instantly download the pass to their Apple Wallet or Google Wallet directly from the confirmation email.') }}
                </flux:text>
            </div>
            <div class="flex gap-3">
                <flux:button href="{{ route('members.index') }}" variant="filled" wire:navigate class="bg-emerald-600 hover:bg-emerald-700 text-white border-none">
                    {{ __('Enroll Member') }}
                </flux:button>
            </div>
        </flux:card>
    </div>
</x-layouts::app>
