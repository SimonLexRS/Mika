<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear tenant de demostración
        $tenant = Tenant::updateOrCreate(
            ['slug' => 'demo'],
            [
                'id' => Str::uuid(),
                'name' => 'Restaurante Demo',
                'business_type' => 'restaurant',
                'tax_id' => 'XAXX010101000',
                'email' => 'demo@mika.app',
                'phone' => '5551234567',
                'address' => 'Av. Reforma 123',
                'city' => 'Ciudad de México',
                'state' => 'CDMX',
                'postal_code' => '06600',
                'country' => 'MX',
                'currency' => 'MXN',
                'timezone' => 'America/Mexico_City',
                'settings' => [
                    'tax_rate' => 16,
                    'print_receipt' => true,
                    'loyalty_enabled' => true,
                    'loyalty_points_per_peso' => 0.1,
                ],
                'plan' => 'premium',
                'is_active' => true,
            ]
        );

        // Crear sucursal principal
        $branch = Branch::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'MAIN'],
            [
                'name' => 'Sucursal Centro',
                'address' => 'Av. Reforma 123, Col. Juárez',
                'city' => 'Ciudad de México',
                'phone' => '5551234567',
                'email' => 'centro@demo.mika.app',
                'is_main' => true,
                'is_active' => true,
            ]
        );

        // Actualizar usuario demo con tenant y branch
        $user = User::where('email', 'demo@mika.app')->first();
        if ($user) {
            $user->update([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'role' => 'owner',
            ]);
        }

        // Crear usuario cajero
        User::updateOrCreate(
            ['email' => 'cajero@demo.mika.app'],
            [
                'name' => 'Cajero Demo',
                'password' => Hash::make('cajero123'),
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'role' => 'cashier',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Establecer tenant actual para los seeders
        app()->instance('current_tenant_id', $tenant->id);

        // Crear categorías
        $categories = [
            ['name' => 'Bebidas', 'slug' => 'bebidas', 'color' => '#3B82F6', 'icon' => '🥤'],
            ['name' => 'Comida', 'slug' => 'comida', 'color' => '#EF4444', 'icon' => '🍔'],
            ['name' => 'Postres', 'slug' => 'postres', 'color' => '#EC4899', 'icon' => '🍰'],
            ['name' => 'Entradas', 'slug' => 'entradas', 'color' => '#10B981', 'icon' => '🥗'],
            ['name' => 'Snacks', 'slug' => 'snacks', 'color' => '#F59E0B', 'icon' => '🍿'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $cat['slug']],
                array_merge($cat, ['tenant_id' => $tenant->id])
            );
        }

        // Obtener categorías creadas
        $bebidas = Category::where('tenant_id', $tenant->id)->where('slug', 'bebidas')->first();
        $comida = Category::where('tenant_id', $tenant->id)->where('slug', 'comida')->first();
        $postres = Category::where('tenant_id', $tenant->id)->where('slug', 'postres')->first();

        // Crear productos
        $products = [
            // Bebidas
            ['name' => 'Coca-Cola 600ml', 'category_id' => $bebidas->id, 'sku' => 'BEB001', 'barcode' => '7501055300518', 'cost' => 12, 'price' => 25, 'unit' => 'pza'],
            ['name' => 'Agua Natural 500ml', 'category_id' => $bebidas->id, 'sku' => 'BEB002', 'cost' => 5, 'price' => 15, 'unit' => 'pza'],
            ['name' => 'Café Americano', 'category_id' => $bebidas->id, 'sku' => 'BEB003', 'cost' => 8, 'price' => 35, 'unit' => 'pza'],
            ['name' => 'Jugo de Naranja', 'category_id' => $bebidas->id, 'sku' => 'BEB004', 'cost' => 15, 'price' => 40, 'unit' => 'pza'],

            // Comida
            ['name' => 'Hamburguesa Clásica', 'category_id' => $comida->id, 'sku' => 'COM001', 'cost' => 35, 'price' => 89, 'unit' => 'pza'],
            ['name' => 'Hamburguesa Doble', 'category_id' => $comida->id, 'sku' => 'COM002', 'cost' => 50, 'price' => 129, 'unit' => 'pza'],
            ['name' => 'Tacos de Pastor (3 pzas)', 'category_id' => $comida->id, 'sku' => 'COM003', 'cost' => 25, 'price' => 65, 'unit' => 'orden'],
            ['name' => 'Quesadilla Sencilla', 'category_id' => $comida->id, 'sku' => 'COM004', 'cost' => 15, 'price' => 45, 'unit' => 'pza'],
            ['name' => 'Papas Fritas', 'category_id' => $comida->id, 'sku' => 'COM005', 'cost' => 12, 'price' => 35, 'unit' => 'orden'],
            ['name' => 'Ensalada César', 'category_id' => $comida->id, 'sku' => 'COM006', 'cost' => 30, 'price' => 75, 'unit' => 'pza'],

            // Postres
            ['name' => 'Pastel de Chocolate', 'category_id' => $postres->id, 'sku' => 'POS001', 'cost' => 20, 'price' => 55, 'unit' => 'reb'],
            ['name' => 'Helado (2 bolas)', 'category_id' => $postres->id, 'sku' => 'POS002', 'cost' => 15, 'price' => 45, 'unit' => 'orden'],
            ['name' => 'Flan Napolitano', 'category_id' => $postres->id, 'sku' => 'POS003', 'cost' => 12, 'price' => 40, 'unit' => 'pza'],
        ];

        foreach ($products as $prod) {
            Product::updateOrCreate(
                ['tenant_id' => $tenant->id, 'sku' => $prod['sku']],
                array_merge($prod, [
                    'tenant_id' => $tenant->id,
                    'tax_rate' => 16,
                    'tax_included' => true,
                    'track_inventory' => true,
                    'low_stock_threshold' => 10,
                    'is_active' => true,
                    'is_for_sale' => true,
                ])
            );
        }

        // Crear clientes de prueba
        $customers = [
            ['name' => 'Juan Pérez', 'phone' => '5551111111', 'email' => 'juan@example.com'],
            ['name' => 'María García', 'phone' => '5552222222', 'email' => 'maria@example.com'],
            ['name' => 'Carlos López', 'phone' => '5553333333', 'tax_id' => 'LOPC850101XXX', 'tax_name' => 'Carlos López Pérez'],
        ];

        foreach ($customers as $cust) {
            Customer::updateOrCreate(
                ['tenant_id' => $tenant->id, 'phone' => $cust['phone']],
                array_merge($cust, ['tenant_id' => $tenant->id])
            );
        }

        $this->command->info('✓ Tenant demo creado con categorías, productos y clientes');
    }
}
