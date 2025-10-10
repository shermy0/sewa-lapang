<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="antialiased bg-gray-100">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="w-full max-w-md bg-white shadow rounded-lg p-6">
                <h1 class="text-2xl font-semibold text-center mb-6">Log in to your account</h1>
                @if (session('status'))
                    <div class="mb-4 rounded-md bg-blue-50 border border-blue-200 px-3 py-2 text-sm text-blue-700">
                        {{ session('status') }}
                    </div>
                @endif
                <form method="POST" action="{{ url('/login') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="password">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center text-sm text-gray-600">
                            <input
                                type="checkbox"
                                name="remember"
                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <span class="ml-2">Remember me</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-2 px-4 rounded-md bg-indigo-600 text-white font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Log in
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-gray-600">
                    Need an account?
                    <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Register</a>
                </p>
            </div>
        </div>
    </body>
</html>
