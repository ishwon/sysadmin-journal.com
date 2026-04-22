<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SysAdmin Journal</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700;800&family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500;600&display=swap">
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
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body x-data="{
    dark: document.documentElement.classList.contains('dark'),
    toggleTheme() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        try { localStorage.theme = this.dark ? 'dark' : 'light'; } catch (e) {}
    }
}" class="font-sans bg-surface-50 text-ink-800 dark:bg-ink-950 dark:text-ink-100 antialiased">
    <div class="min-h-screen flex">
        {{-- Sidebar --}}
        <aside class="w-64 bg-ink-900 text-white flex-shrink-0 border-r border-ink-800 flex flex-col">
            <div class="px-6 py-5 border-b border-ink-800">
                <a href="/" class="inline-flex items-center gap-2 text-base font-extrabold text-white hover:text-accent-400 transition-colors">
                    <svg class="h-5 w-5 text-accent-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M3 4a2 2 0 012-2h10a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V4zm3 2h8v2H6V6zm0 4h8v2H6v-2zm0 4h5v2H6v-2z" />
                    </svg>
                    SysAdmin Journal
                </a>
            </div>
            <nav class="flex-1 py-3 overflow-y-auto">
                <x-ui.nav-item href="{{ route('dashboard.index') }}" :active="request()->routeIs('dashboard.index')"
                    :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6\'/></svg>'">
                    Dashboard
                </x-ui.nav-item>
                <x-ui.nav-item href="{{ route('dashboard.posts.index') }}" :active="request()->routeIs('dashboard.posts.*')"
                    :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z\'/></svg>'">
                    Posts
                </x-ui.nav-item>
                <x-ui.nav-item href="{{ route('dashboard.pages.index') }}" :active="request()->routeIs('dashboard.pages.*')"
                    :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\'/></svg>'">
                    Pages
                </x-ui.nav-item>
                <x-ui.nav-item href="{{ route('dashboard.galleries.index') }}" :active="request()->routeIs('dashboard.galleries.*')"
                    :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'/></svg>'">
                    Galleries
                </x-ui.nav-item>
                <x-ui.nav-item href="{{ route('dashboard.media.index') }}" :active="request()->routeIs('dashboard.media.*')"
                    :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z\'/></svg>'">
                    Media
                </x-ui.nav-item>
                <x-ui.nav-item href="{{ route('dashboard.tags.index') }}" :active="request()->routeIs('dashboard.tags.*')"
                    :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z\'/></svg>'">
                    Tags
                </x-ui.nav-item>
                <x-ui.nav-item href="{{ route('dashboard.users.index') }}" :active="request()->routeIs('dashboard.users.*')"
                    :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\'/></svg>'">
                    Users
                </x-ui.nav-item>
            </nav>
            <div class="border-t border-ink-800 px-4 py-3 text-xs text-ink-400 font-mono">
                v1.0 · {{ app()->environment() }}
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Topbar --}}
            <header class="bg-white dark:bg-ink-900 border-b border-surface-200 dark:border-ink-800 sticky top-0 z-30">
                <div class="flex items-center justify-between gap-4 px-6 py-3">
                    <div class="min-w-0">
                        @hasSection('breadcrumbs')
                            @yield('breadcrumbs')
                        @endif
                        <h1 class="text-lg font-semibold text-ink-900 dark:text-ink-50 truncate">@yield('title', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        @yield('actions')
                        <x-ui.theme-toggle />
                        <a href="/" target="_blank" class="inline-flex h-9 items-center gap-1.5 rounded-md px-3 text-sm font-medium text-ink-500 hover:text-ink-900 hover:bg-surface-100 dark:text-ink-300 dark:hover:text-ink-50 dark:hover:bg-ink-800 transition-colors" title="View public site">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 00-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 00.75-.75v-4a.75.75 0 011.5 0v4A2.25 2.25 0 0112.75 17h-8.5A2.25 2.25 0 012 14.75v-8.5A2.25 2.25 0 014.25 4h5a.75.75 0 010 1.5h-5z"/><path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 001.06.053L16.5 4.44v2.81a.75.75 0 001.5 0v-4.5a.75.75 0 00-.75-.75h-4.5a.75.75 0 000 1.5h2.553l-9.056 8.194a.75.75 0 00-.053 1.06z" clip-rule="evenodd"/></svg>
                            <span class="hidden sm:inline">View site</span>
                        </a>

                        <x-ui.dropdown>
                            <x-slot:trigger>
                                <button type="button" class="inline-flex items-center gap-2 rounded-full border border-surface-200 bg-white px-2 py-1 text-sm hover:border-ink-300 dark:bg-ink-900 dark:border-ink-700 dark:hover:border-ink-600 transition-colors">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-ink-800 text-white text-xs font-semibold">
                                        {{ Str::upper(Str::substr(auth()->user()->name ?? 'U', 0, 2)) }}
                                    </span>
                                    <span class="pr-1 text-ink-800 dark:text-ink-100 hidden sm:inline">{{ auth()->user()->name ?? 'User' }}</span>
                                </button>
                            </x-slot:trigger>
                            <div class="px-4 py-2 border-b border-surface-200 dark:border-ink-700">
                                <p class="text-xs text-ink-500 dark:text-ink-400">Signed in as</p>
                                <p class="text-sm font-medium text-ink-900 dark:text-ink-50 truncate">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                            <x-ui.dropdown-item href="{{ auth()->check() ? route('dashboard.users.edit', auth()->user()) : '#' }}">Edit profile</x-ui.dropdown-item>
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <x-ui.dropdown-item type="submit" tone="danger">Sign out</x-ui.dropdown-item>
                            </form>
                        </x-ui.dropdown>
                    </div>
                </div>
            </header>

            {{-- Content --}}
            <main class="flex-1 p-6">
                @if (session('success'))
                    <div class="mb-4">
                        <x-ui.alert tone="success" dismissible>{{ session('success') }}</x-ui.alert>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4">
                        <x-ui.alert tone="danger" dismissible>{{ session('error') }}</x-ui.alert>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4">
                        <x-ui.alert tone="danger" title="Please fix the following:">
                            <ul class="list-disc list-inside mt-1 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-ui.alert>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
