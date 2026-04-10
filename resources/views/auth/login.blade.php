<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SysAdmin Journal</title>
    <link rel="stylesheet" href="https://use.typekit.net/ikg3vvf.css">
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans bg-gray-900 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-extrabold text-white">SysAdmin Journal</h1>
            <p class="mt-2 text-gray-400">Sign in to your dashboard</p>
        </div>
        <div class="bg-white rounded-lg shadow-xl p-8">
            @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
                {{ $errors->first() }}
            </div>
            @endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div class="mb-6 flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-emerald-600 border-gray-300 rounded">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
                </div>
                <button type="submit" class="w-full bg-emerald-500 text-white py-2 px-4 rounded-md font-medium hover:bg-emerald-600 transition duration-300">Sign in</button>
            </form>
        </div>
    </div>
</body>
</html>
