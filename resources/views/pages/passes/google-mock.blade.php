<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Save to Google Wallet (Simulated)</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-zinc-50 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-100 min-h-screen flex flex-col justify-between font-sans">
    
    <!-- Header -->
    <header class="p-6 border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">💳</span>
                <span class="font-bold text-lg tracking-wide">Google Wallet Sandbox</span>
            </div>
            <a href="/" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                Back to Site
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-lg mx-auto w-full px-6 py-12 flex flex-col items-center justify-center">
        <!-- Success Alert -->
        <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mb-6 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </div>

        <h1 class="text-2xl font-extrabold text-center mb-2 tracking-tight">Save to Google Wallet</h1>
        <p class="text-zinc-500 dark:text-zinc-400 text-center text-sm mb-8">
            You have successfully simulated saving the membership card for <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ $member->name }}</span> to Google Wallet.
        </p>

        <!-- Simulated Card Preview -->
        <div class="w-full bg-gradient-to-br from-emerald-800 to-emerald-950 rounded-2xl p-6 text-white shadow-xl border border-emerald-700/30 relative overflow-hidden mb-8">
            <div class="flex justify-between items-start border-b border-white/10 pb-4 mb-6">
                <div>
                    <p class="text-[10px] uppercase font-bold tracking-widest text-emerald-300">Bahamas National Trust</p>
                    <p class="text-lg font-bold">Membership Pass</p>
                </div>
                <span class="text-2xl">🇧🇸</span>
            </div>

            <div class="mb-6">
                <p class="text-xs text-white/60 uppercase tracking-wider mb-0.5">Member Name</p>
                <p class="text-xl font-bold tracking-wide">{{ $member->name }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] text-white/60 uppercase tracking-wider mb-0.5">Member ID</p>
                    <p class="text-sm font-semibold">{{ $member->membership_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-emerald-300 uppercase tracking-wider mb-0.5">Membership Tier</p>
                    <p class="text-sm font-bold text-amber-400 uppercase tracking-wider">{{ $member->tier }}</p>
                </div>
            </div>
        </div>

        <!-- Sandbox Note -->
        <div class="w-full bg-amber-50 dark:bg-amber-950/30 border border-amber-200/50 dark:border-amber-900/50 rounded-xl p-4 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-3">
            <span class="text-lg">ℹ️</span>
            <div>
                <p class="font-bold mb-1">Developer Sandbox Note</p>
                <p class="leading-relaxed">
                    In a production environment with Google Wallet credentials configured, this button signs a JWT payload and redirects the user to the official Google Wallet Save URL:
                </p>
                <code class="block mt-2 p-1.5 bg-black/5 dark:bg-black/30 rounded font-mono text-[10px] break-all">
                    https://pay.google.com/gp/v/save/{signed_jwt}
                </code>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="p-6 border-t border-zinc-200 dark:border-zinc-800 text-center text-xs text-zinc-400 dark:text-zinc-500 bg-white dark:bg-zinc-950">
        &copy; {{ date('Y') }} Bahamas National Trust. All rights reserved.
    </footer>

</body>
</html>
