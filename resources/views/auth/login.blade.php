<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — SysAdmin Journal</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600&family=Newsreader:ital,opsz,wght@0,6..72,500;1,6..72,400&family=JetBrains+Mono:wght@400;500&family=Noto+Serif+Devanagari:wght@400&display=swap">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans bg-white text-ink-700 antialiased">
    <div class="min-h-screen grid lg:grid-cols-[5fr_7fr]">
        <div class="hidden lg:flex bg-ink-900 text-surface-100 p-12 flex-col justify-between">
            <a href="/" class="font-display text-[22px] font-medium text-white">SysAdmin Journal<span class="text-accent-300">.</span></a>
            <div>
                <p class="font-deva text-[28px] leading-normal text-white">विद्या ददाति विनयं</p>
                <p class="mt-2 font-display italic text-lg text-ink-300">vidyā dadāti vinayaṃ — knowledge gives humility.</p>
            </div>
            <p class="font-mono text-xs tracking-wide text-ink-400">/dashboard/ · v2.0</p>
        </div>

        <div class="flex items-center justify-center p-8 lg:p-12">
            <form method="POST" action="{{ route('login') }}" class="w-full max-w-[360px] flex flex-col gap-5">
                @csrf
                <div>
                    <p class="eyebrow">/login/</p>
                    <h1 class="display text-4xl mt-2">Sign in</h1>
                </div>
                @if ($errors->any())
                    <x-ui.alert tone="danger">{{ $errors->first() }}</x-ui.alert>
                @endif
                <x-ui.input name="email" type="email" label="Email" :value="old('email')" required autofocus class="py-2.5" />
                <x-ui.input name="password" type="password" label="Password" required class="py-2.5" />
                <x-ui.checkbox name="remember" label="Remember me" />
                <x-ui.button type="submit" variant="primary" size="lg" class="w-full h-11">Sign in</x-ui.button>
                <p class="meta"><a href="/" class="link-rise">← back to site</a></p>
            </form>
        </div>
    </div>
</body>
</html>
