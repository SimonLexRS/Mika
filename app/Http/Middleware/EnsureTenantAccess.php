<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que hay un tenant identificado
        if (!app()->bound('current_tenant')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Tenant not identified',
                    'message' => 'No se pudo identificar el negocio.',
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Por favor inicia sesión para continuar.');
        }

        $tenant = app('current_tenant');

        // Verificar que el tenant está activo
        if (!$tenant->is_active) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Tenant inactive',
                    'message' => 'Tu cuenta está desactivada.',
                ], 403);
            }

            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Tu cuenta está desactivada. Contacta soporte.');
        }

        return $next($request);
    }
}
