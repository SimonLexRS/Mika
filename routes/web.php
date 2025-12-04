<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('chat');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/chat', function () {
        return view('pages.chat');
    })->name('chat');

    Route::get('/scanner', function () {
        return view('pages.scanner');
    })->name('scanner');

    Route::get('/profile', function () {
        return view('pages.profile');
    })->name('profile');
});

// Rutas de autenticación temporales para desarrollo
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function () {
    $credentials = request()->only('email', 'password');

    if (auth()->attempt($credentials)) {
        request()->session()->regenerate();
        return redirect()->intended('chat');
    }

    return back()->withErrors([
        'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
    ]);
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', function () {
    $validated = request()->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = \App\Models\User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
    ]);

    auth()->login($user);

    return redirect()->route('chat');
});
