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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Source+Serif+4:opsz,ital,wght@8..60,0,400;8..60,0,500;8..60,1,400&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400&family=JetBrains+Mono:ital,wght@0,400;0,500;0,600&family=Noto+Serif+Devanagari:wght@400;500&display=swap">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans bg-white text-ink-700 antialiased">
    <div class="min-h-screen flex">
        {{-- Sidebar: 240px, deep blue --}}
        <aside class="w-60 bg-ink-900 text-surface-100 flex-shrink-0 flex flex-col">
            <div class="h-14 px-6 flex items-center border-b border-ink-800">
                <a href="/" class="font-display text-lg font-medium text-white">SysAdmin Journal<span class="text-accent-300">.</span></a>
            </div>
            <nav class="flex-1 p-3 flex flex-col gap-0.5 overflow-y-auto">
                <x-ui.nav-item href="{{ route('dashboard.index') }}" :active="request()->routeIs('dashboard.index')"
                    :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.75\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6\'/></svg>'">
                    Dashboard
                </x-ui.nav-item>
                <x-ui.nav-item href="{{ route('dashboard.posts.index') }}" :active="request()->routeIs('dashboard.posts.*')" :count="$navCounts['posts'] ?? null"
                    :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.75\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z\'/></svg>'">
                    Posts
                </x-ui.nav-item>
                <x-ui.nav-item href="{{ route('dashboard.pages.index') }}" :active="request()->routeIs('dashboard.pages.*')" :count="$navCounts['pages'] ?? null"
                    :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.75\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z\'/></svg>'">
                    Pages
                </x-ui.nav-item>
                <x-ui.nav-item href="{{ route('dashboard.galleries.index') }}" :active="request()->routeIs('dashboard.galleries.*')" :count="$navCounts['galleries'] ?? null"
                    :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.75\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'/></svg>'">
                    Galleries
                </x-ui.nav-item>
                <x-ui.nav-item href="{{ route('dashboard.media.index') }}" :active="request()->routeIs('dashboard.media.*')"
                    :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.75\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z\'/></svg>'">
                    Media
                </x-ui.nav-item>
                <x-ui.nav-item href="{{ route('dashboard.tags.index') }}" :active="request()->routeIs('dashboard.tags.*')" :count="$navCounts['tags'] ?? null"
                    :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.75\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z\'/></svg>'">
                    Tags
                </x-ui.nav-item>
                <x-ui.nav-item href="{{ route('dashboard.users.index') }}" :active="request()->routeIs('dashboard.users.*')" :count="$navCounts['users'] ?? null"
                    :icon="'<svg viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.75\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z\'/></svg>'">
                    Users
                </x-ui.nav-item>
            </nav>
            <x-ui.dropdown align="left" width="56">
                <x-slot:trigger>
                    <button type="button" class="w-full px-6 py-4 border-t border-ink-800 flex items-center gap-2.5 text-left hover:bg-ink-800 transition-colors">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-ink-700 text-white font-display text-xs">
                            {{ Str::upper(Str::substr(auth()->user()->name ?? 'U', 0, 2)) }}
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[13px] font-medium text-white truncate">{{ auth()->user()->name ?? 'User' }}</span>
                            <span class="block font-mono text-[11px] text-ink-400">v2.0 · {{ app()->environment() }}</span>
                        </span>
                    </button>
                </x-slot:trigger>
                <div class="px-4 py-2 border-b border-ink-100">
                    <p class="font-mono text-[11px] text-ink-400">signed in as</p>
                    <p class="text-sm text-ink-900 truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <x-ui.dropdown-item href="{{ auth()->check() ? route('dashboard.users.edit', auth()->user()) : '#' }}">Edit profile</x-ui.dropdown-item>
                <form method="POST" action="{{ route('logout') }}" class="block">
                    @csrf
                    <x-ui.dropdown-item type="submit" tone="danger">Sign out</x-ui.dropdown-item>
                </form>
            </x-ui.dropdown>
        </aside>

        {{-- Main --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-14 bg-white border-b border-ink-100 sticky top-0 z-30 flex items-center justify-between gap-4 px-8">
                <div class="min-w-0 font-mono text-xs tracking-wide text-ink-400 truncate">
                    @hasSection('breadcrumbs')
                        @yield('breadcrumbs')
                    @else
                        {{ '/' . trim(request()->path(), '/') . '/' }}
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @yield('actions')
                    <a href="/" target="_blank" class="inline-flex h-8 items-center gap-1.5 rounded-sm px-2.5 ml-2 text-[13px] font-medium text-ink-500 hover:text-ink-900 hover:bg-surface-50 transition-colors" title="View public site">
                        View site ↗
                    </a>
                </div>
            </header>

            <main class="flex-1 px-8 pt-10 pb-16 w-full max-w-[1120px]">
                @if (session('success'))
                    <div class="mb-6"><x-ui.alert tone="success" dismissible>{{ session('success') }}</x-ui.alert></div>
                @endif
                @if (session('error'))
                    <div class="mb-6"><x-ui.alert tone="danger" dismissible>{{ session('error') }}</x-ui.alert></div>
                @endif
                @if ($errors->any())
                    <div class="mb-6">
                        <x-ui.alert tone="danger" title="Please fix the following:">
                            <ul class="list-disc list-inside mt-1 space-y-0.5">
                                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </x-ui.alert>
                    </div>
                @endif

                @hasSection('page-title')
                    <div class="flex items-end justify-between gap-6 pb-6 border-b border-ink-100 mb-0">
                        <h1 class="display text-4xl">@yield('page-title')</h1>
                        @hasSection('page-aside')<div>@yield('page-aside')</div>@endif
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
