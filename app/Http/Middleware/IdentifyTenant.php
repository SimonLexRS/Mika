<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;

        // Intentar identificar tenant por usuario autenticado
        if ($request->user() && $request->user()->tenant_id) {
            $tenant = Tenant::find($request->user()->tenant_id);
        }

        // Intentar identificar por subdominio
        if (!$tenant) {
            $host = $request->getHost();
            $subdomain = explode('.', $host)[0];

            if ($subdomain && $subdomain !== 'www' && $subdomain !== 'mika') {
                $tenant = Tenant::where('slug', $subdomain)
                    ->where('is_active', true)
                    ->first();
            }
        }

        // Intentar identificar por header (para APIs)
        if (!$tenant && $request->hasHeader('X-Tenant-ID')) {
            $tenant = Tenant::where('id', $request->header('X-Tenant-ID'))
                ->where('is_active', true)
                ->first();
        }

        if ($tenant) {
            // Registrar el tenant actual en el contenedor
            app()->instance('current_tenant', $tenant);
            app()->instance('current_tenant_id', $tenant->id);

            // Establecer timezone del tenant
            config(['app.timezone' => $tenant->timezone]);
            date_default_timezone_set($tenant->timezone);
        }

        return $next($request);
    }
}
