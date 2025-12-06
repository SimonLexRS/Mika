<?php

namespace App\Livewire\Pos;

use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Terminal extends Component
{
    // Búsqueda
    public string $search = '';

    // Carrito
    public array $cart = [];

    // Cliente
    public ?int $customerId = null;
    public string $customerSearch = '';

    // Pago
    public string $paymentMethod = 'cash';
    public float $amountPaid = 0;
    public string $notes = '';

    // Modales
    public bool $showPaymentModal = false;
    public bool $showCustomerModal = false;
    public bool $showProductModal = false;
    public ?int $selectedProductId = null;

    // Descuento
    public float $discountAmount = 0;
    public string $discountType = 'amount'; // amount, percent

    protected $rules = [
        'amountPaid' => 'required|numeric|min:0',
        'paymentMethod' => 'required|in:cash,card,transfer,mixed',
    ];

    public function mount()
    {
        // Verificar que hay caja abierta
        if (!$this->cashRegister) {
            session()->flash('warning', 'No hay caja abierta. Por favor abre la caja para continuar.');
        }
    }

    #[Computed]
    public function branch()
    {
        return auth()->user()->branch;
    }

    #[Computed]
    public function cashRegister()
    {
        return CashRegister::where('branch_id', $this->branch?->id)
            ->where('status', 'open')
            ->first();
    }

    #[Computed]
    public function products()
    {
        if (strlen($this->search) < 2) {
            return collect();
        }

        return Product::query()
            ->active()
            ->forSale()
            ->search($this->search)
            ->with('category')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function customers()
    {
        if (strlen($this->customerSearch) < 2) {
            return collect();
        }

        return Customer::query()
            ->active()
            ->search($this->customerSearch)
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function customer()
    {
        return $this->customerId ? Customer::find($this->customerId) : null;
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->cart)->sum(function ($item) {
            return ($item['price'] * $item['quantity']) - ($item['discount'] ?? 0);
        });
    }

    #[Computed]
    public function taxTotal(): float
    {
        return collect($this->cart)->sum(function ($item) {
            $subtotal = ($item['price'] * $item['quantity']) - ($item['discount'] ?? 0);
            return $subtotal * ($item['tax_rate'] / 100);
        });
    }

    #[Computed]
    public function discount(): float
    {
        if ($this->discountType === 'percent') {
            return $this->subtotal * ($this->discountAmount / 100);
        }

        return $this->discountAmount;
    }

    #[Computed]
    public function total(): float
    {
        return max(0, $this->subtotal + $this->taxTotal - $this->discount);
    }

    #[Computed]
    public function change(): float
    {
        return max(0, $this->amountPaid - $this->total);
    }

    #[Computed]
    public function itemCount(): int
    {
        return collect($this->cart)->sum('quantity');
    }

    /**
     * Agregar producto al carrito.
     */
    public function addToCart(int $productId, float $quantity = 1)
    {
        $product = Product::find($productId);

        if (!$product || !$product->is_for_sale) {
            $this->dispatch('notify', type: 'error', message: 'Producto no disponible');
            return;
        }

        $key = "product_{$productId}";

        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity'] += $quantity;
        } else {
            $this->cart[$key] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'cost' => $product->cost,
                'quantity' => $quantity,
                'unit' => $product->unit,
                'tax_rate' => $product->tax_rate,
                'tax_included' => $product->tax_included,
                'discount' => 0,
            ];
        }

        $this->search = '';
        $this->dispatch('notify', type: 'success', message: 'Producto agregado');
    }

    /**
     * Buscar por código de barras.
     */
    public function searchBarcode(string $barcode)
    {
        $product = Product::where('barcode', $barcode)
            ->active()
            ->forSale()
            ->first();

        if ($product) {
            $this->addToCart($product->id);
        } else {
            $this->dispatch('notify', type: 'error', message: 'Producto no encontrado');
        }
    }

    /**
     * Actualizar cantidad de un item.
     */
    public function updateQuantity(string $key, float $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($key);
            return;
        }

        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity'] = $quantity;
        }
    }

    /**
     * Aplicar descuento a un item.
     */
    public function applyItemDiscount(string $key, float $discount)
    {
        if (isset($this->cart[$key])) {
            $maxDiscount = $this->cart[$key]['price'] * $this->cart[$key]['quantity'];
            $this->cart[$key]['discount'] = min($discount, $maxDiscount);
        }
    }

    /**
     * Eliminar item del carrito.
     */
    public function removeFromCart(string $key)
    {
        unset($this->cart[$key]);
    }

    /**
     * Vaciar carrito.
     */
    public function clearCart()
    {
        $this->cart = [];
        $this->customerId = null;
        $this->discountAmount = 0;
        $this->notes = '';
    }

    /**
     * Seleccionar cliente.
     */
    public function selectCustomer(int $customerId)
    {
        $this->customerId = $customerId;
        $this->customerSearch = '';
        $this->showCustomerModal = false;
    }

    /**
     * Remover cliente.
     */
    public function removeCustomer()
    {
        $this->customerId = null;
    }

    /**
     * Abrir modal de pago.
     */
    public function openPaymentModal()
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', type: 'error', message: 'El carrito está vacío');
            return;
        }

        if (!$this->cashRegister) {
            $this->dispatch('notify', type: 'error', message: 'No hay caja abierta');
            return;
        }

        $this->amountPaid = $this->total;
        $this->showPaymentModal = true;
    }

    /**
     * Procesar venta.
     */
    public function processSale()
    {
        $this->validate();

        if (empty($this->cart)) {
            $this->dispatch('notify', type: 'error', message: 'El carrito está vacío');
            return;
        }

        if ($this->paymentMethod === 'cash' && $this->amountPaid < $this->total) {
            $this->dispatch('notify', type: 'error', message: 'El monto pagado es insuficiente');
            return;
        }

        try {
            $sale = DB::transaction(function () {
                // Crear la venta
                $sale = Sale::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'branch_id' => $this->branch->id,
                    'cash_register_id' => $this->cashRegister->id,
                    'customer_id' => $this->customerId,
                    'user_id' => auth()->id(),
                    'type' => Sale::TYPE_SALE,
                    'status' => Sale::STATUS_COMPLETED,
                    'subtotal' => $this->subtotal,
                    'tax' => $this->taxTotal,
                    'discount' => $this->discount,
                    'discount_type' => $this->discountType,
                    'total' => $this->total,
                    'paid' => $this->amountPaid,
                    'change' => $this->change,
                    'payment_method' => $this->paymentMethod,
                    'notes' => $this->notes,
                ]);

                // Crear items de la venta
                foreach ($this->cart as $item) {
                    $itemSubtotal = ($item['price'] * $item['quantity']) - ($item['discount'] ?? 0);
                    $itemTax = $itemSubtotal * ($item['tax_rate'] / 100);

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['name'],
                        'product_sku' => $item['sku'],
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'],
                        'unit_price' => $item['price'],
                        'cost' => $item['cost'],
                        'discount' => $item['discount'] ?? 0,
                        'tax_rate' => $item['tax_rate'],
                        'tax' => $itemTax,
                        'subtotal' => $itemSubtotal,
                        'total' => $itemSubtotal + $itemTax,
                    ]);
                }

                // Registrar en caja
                $sale->complete();

                // Reducir inventario
                app(InventoryService::class)->processSale($sale);

                // Agregar puntos de lealtad al cliente
                if ($sale->customer) {
                    $points = (int) floor($sale->total / 10); // 1 punto por cada $10
                    $sale->customer->addLoyaltyPoints($points);
                }

                return $sale;
            });

            $this->showPaymentModal = false;
            $this->clearCart();

            $this->dispatch('notify', type: 'success', message: "Venta #{$sale->ticket_number} completada");
            $this->dispatch('sale-completed', saleId: $sale->id);

        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error al procesar la venta: ' . $e->getMessage());
        }
    }

    /**
     * Agregar pago rápido.
     */
    public function quickPay(string $method)
    {
        $this->paymentMethod = $method;
        $this->amountPaid = $this->total;
        $this->processSale();
    }

    public function render()
    {
        return view('livewire.pos.terminal')
            ->layout('layouts.pos');
    }
}
