<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — SysAdmin Journal</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap">
    <script>
        (function () {
            try {
                var s = localStorage.theme;
                if (s === 'dark' || (!s && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans bg-ink-900 text-ink-100 min-h-screen flex items-center justify-center antialiased">
    <div class="max-w-md w-full mx-4">
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 text-white">
                <svg class="h-6 w-6 text-accent-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M3 4a2 2 0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V4zm3 2h8v2H6V6zm0 4h8v2H6v-2zm0 4h5v2H6v-2z" />
                </svg>
                <h1 class="text-2xl font-extrabold">SysAdmin Journal</h1>
            </div>
            <p class="mt-2 text-sm text-ink-300">Sign in to your dashboard</p>
        </div>

        <div class="bg-white dark:bg-ink-800 rounded-lg shadow-xl p-8 border border-ink-700/50">
            @if ($errors->any())
                <div class="mb-4">
                    <x-ui.alert tone="danger">{{ $errors->first() }}</x-ui.alert>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <x-ui.input name="email" type="email" label="Email" :value="old('email')" required autofocus />
                <x-ui.input name="password" type="password" label="Password" required />
                <div class="pt-1">
                    <x-ui.checkbox name="remember" label="Remember me" />
                </div>
                <x-ui.button type="submit" variant="primary" class="w-full">Sign in</x-ui.button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-ink-400">
            <a href="/" class="hover:text-accent-400 transition-colors">&larr; Back to site</a>
        </p>
    </div>
</body>
</html>
