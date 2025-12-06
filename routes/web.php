<?php

use App\Livewire\Pos\Terminal;
use App\Livewire\Pos\CashRegisterOpen;
use App\Livewire\Pos\CashRegisterClose;
use App\Livewire\Inventory\ProductList;
use App\Livewire\Inventory\ProductForm;
use App\Livewire\Reports\SalesDashboard;
use Illuminate\Support\Facades\Route;

// Healthcheck endpoint para Docker/Dokploy
Route::get('/up', fn() => response('OK', 200));

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard principal
    Route::get('/dashboard', function () {
        return view('pages.dashboard');
    })->name('dashboard');

    // Chat (funcionalidad original de Mika)
    Route::get('/chat', function () {
        return view('pages.chat');
    })->name('chat');

    Route::get('/scanner', function () {
        return view('pages.scanner');
    })->name('scanner');

    Route::get('/profile', function () {
        return view('pages.profile');
    })->name('profile');

    // =========================================================================
    // ERP Routes
    // =========================================================================

    // POS (Punto de Venta)
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', Terminal::class)->name('terminal');
        Route::get('/cash-register/open', CashRegisterOpen::class)->name('cash-register.open');
        Route::get('/cash-register/close', CashRegisterClose::class)->name('cash-register.close');
    });

    // Inventario y Productos
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/products', ProductList::class)->name('products');
        Route::get('/products/create', ProductForm::class)->name('products.create');
        Route::get('/products/{product}/edit', ProductForm::class)->name('products.edit');
    });

    // Reportes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', SalesDashboard::class)->name('sales');
    });
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
