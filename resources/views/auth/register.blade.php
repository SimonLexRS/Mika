<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#121212">
    <title>Registro - Mika</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-mika-bg min-h-screen flex flex-col items-center justify-center px-4 py-8">
    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-mika-primary mx-auto flex items-center justify-center mb-4">
                <x-mika-avatar class="w-12 h-12" />
            </div>
            <h1 class="text-2xl font-bold text-white">Crear cuenta</h1>
            <p class="text-mika-text-secondary">Comienza a organizar tus finanzas</p>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="bg-mika-danger/20 border border-mika-danger rounded-lg p-4 mb-6">
                @foreach ($errors->all() as $error)
                    <p class="text-mika-danger text-sm">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm text-mika-text-secondary mb-2">Nombre</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    class="w-full bg-mika-surface text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-mika-primary"
                    placeholder="Tu nombre"
                >
            </div>

            <div>
                <label for="email" class="block text-sm text-mika-text-secondary mb-2">Correo electrónico</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full bg-mika-surface text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-mika-primary"
                    placeholder="tu@email.com"
                >
            </div>

            <div>
                <label for="password" class="block text-sm text-mika-text-secondary mb-2">Contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="w-full bg-mika-surface text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-mika-primary"
                    placeholder="Mínimo 8 caracteres"
                >
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm text-mika-text-secondary mb-2">Confirmar contraseña</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    class="w-full bg-mika-surface text-white rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-mika-primary"
                    placeholder="Repite tu contraseña"
                >
            </div>

            <button type="submit" class="w-full btn-primary py-3 text-center font-semibold">
                Crear cuenta
            </button>
        </form>

        <p class="text-center text-mika-text-secondary text-sm mt-6">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="text-mika-primary hover:text-mika-primary-light">
                Inicia sesión
            </a>
        </p>
    </div>
</body>
</html>
