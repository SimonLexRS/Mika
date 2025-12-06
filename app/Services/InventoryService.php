<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Agregar stock a un producto.
     */
    public function addStock(
        Product $product,
        Branch $branch,
        float $quantity,
        ?float $cost = null,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): InventoryMovement {
        return DB::transaction(function () use ($product, $branch, $quantity, $cost, $notes, $referenceType, $referenceId) {
            $stock = $this->getOrCreateStock($product, $branch);
            $quantityBefore = $stock->quantity;

            $stock->addStock($quantity);

            return InventoryMovement::create([
                'tenant_id' => $product->tenant_id,
                'product_id' => $product->id,
                'branch_id' => $branch->id,
                'user_id' => auth()->id(),
                'type' => InventoryMovement::TYPE_IN,
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $stock->quantity,
                'cost' => $cost ?? $product->cost,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Reducir stock de un producto.
     */
    public function reduceStock(
        Product $product,
        Branch $branch,
        float $quantity,
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): ?InventoryMovement {
        return DB::transaction(function () use ($product, $branch, $quantity, $notes, $referenceType, $referenceId) {
            $stock = $this->getOrCreateStock($product, $branch);
            $quantityBefore = $stock->quantity;

            if (!$stock->reduceStock($quantity)) {
                return null; // No hay suficiente stock
            }

            return InventoryMovement::create([
                'tenant_id' => $product->tenant_id,
                'product_id' => $product->id,
                'branch_id' => $branch->id,
                'user_id' => auth()->id(),
                'type' => InventoryMovement::TYPE_OUT,
                'quantity' => -$quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $stock->quantity,
                'cost' => $product->cost,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Ajustar stock (positivo o negativo).
     */
    public function adjustStock(
        Product $product,
        Branch $branch,
        float $newQuantity,
        ?string $notes = null
    ): InventoryMovement {
        return DB::transaction(function () use ($product, $branch, $newQuantity, $notes) {
            $stock = $this->getOrCreateStock($product, $branch);
            $quantityBefore = $stock->quantity;
            $difference = $newQuantity - $quantityBefore;

            $stock->update(['quantity' => $newQuantity]);

            return InventoryMovement::create([
                'tenant_id' => $product->tenant_id,
                'product_id' => $product->id,
                'branch_id' => $branch->id,
                'user_id' => auth()->id(),
                'type' => InventoryMovement::TYPE_ADJUSTMENT,
                'quantity' => $difference,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $newQuantity,
                'cost' => $product->cost,
                'notes' => $notes ?? 'Ajuste de inventario',
            ]);
        });
    }

    /**
     * Transferir stock entre sucursales.
     */
    public function transferStock(
        Product $product,
        Branch $fromBranch,
        Branch $toBranch,
        float $quantity,
        ?string $notes = null
    ): array {
        return DB::transaction(function () use ($product, $fromBranch, $toBranch, $quantity, $notes) {
            $fromStock = $this->getOrCreateStock($product, $fromBranch);
            $toStock = $this->getOrCreateStock($product, $toBranch);

            $fromBefore = $fromStock->quantity;
            $toBefore = $toStock->quantity;

            if (!$fromStock->reduceStock($quantity)) {
                throw new \Exception('No hay suficiente stock para transferir.');
            }

            $toStock->addStock($quantity);

            $outMovement = InventoryMovement::create([
                'tenant_id' => $product->tenant_id,
                'product_id' => $product->id,
                'branch_id' => $fromBranch->id,
                'user_id' => auth()->id(),
                'type' => InventoryMovement::TYPE_TRANSFER_OUT,
                'quantity' => -$quantity,
                'quantity_before' => $fromBefore,
                'quantity_after' => $fromStock->quantity,
                'cost' => $product->cost,
                'notes' => $notes ?? "Transferencia a {$toBranch->name}",
            ]);

            $inMovement = InventoryMovement::create([
                'tenant_id' => $product->tenant_id,
                'product_id' => $product->id,
                'branch_id' => $toBranch->id,
                'user_id' => auth()->id(),
                'type' => InventoryMovement::TYPE_TRANSFER_IN,
                'quantity' => $quantity,
                'quantity_before' => $toBefore,
                'quantity_after' => $toStock->quantity,
                'cost' => $product->cost,
                'notes' => $notes ?? "Transferencia desde {$fromBranch->name}",
            ]);

            return ['out' => $outMovement, 'in' => $inMovement];
        });
    }

    /**
     * Procesar venta (reducir stock de todos los items).
     */
    public function processSale(Sale $sale): void
    {
        if (!$sale->isCompleted()) {
            return;
        }

        foreach ($sale->items as $item) {
            if (!$item->product || !$item->product->track_inventory) {
                continue;
            }

            $this->reduceStock(
                $item->product,
                $sale->branch,
                $item->quantity,
                "Venta #{$sale->ticket_number}",
                'sale',
                $sale->id
            );
        }
    }

    /**
     * Revertir venta (devolver stock de todos los items).
     */
    public function revertSale(Sale $sale): void
    {
        foreach ($sale->items as $item) {
            if (!$item->product || !$item->product->track_inventory) {
                continue;
            }

            $this->addStock(
                $item->product,
                $sale->branch,
                $item->quantity,
                $item->cost,
                "Devolución venta #{$sale->ticket_number}",
                'sale',
                $sale->id
            );
        }
    }

    /**
     * Obtener o crear registro de stock.
     */
    protected function getOrCreateStock(Product $product, Branch $branch): InventoryStock
    {
        return InventoryStock::firstOrCreate(
            [
                'product_id' => $product->id,
                'branch_id' => $branch->id,
            ],
            [
                'tenant_id' => $product->tenant_id,
                'quantity' => 0,
                'reserved' => 0,
                'min_stock' => $product->low_stock_threshold,
            ]
        );
    }

    /**
     * Obtener productos con stock bajo.
     */
    public function getLowStockProducts(Branch $branch): \Illuminate\Database\Eloquent\Collection
    {
        return Product::query()
            ->where('track_inventory', true)
            ->whereNotNull('low_stock_threshold')
            ->whereHas('inventoryStocks', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                    ->whereRaw('quantity - reserved <= (SELECT low_stock_threshold FROM products WHERE products.id = inventory_stocks.product_id)');
            })
            ->with(['inventoryStocks' => fn ($q) => $q->where('branch_id', $branch->id)])
            ->get();
    }

    /**
     * Obtener valor total del inventario.
     */
    public function getInventoryValue(Branch $branch): array
    {
        $stocks = InventoryStock::where('branch_id', $branch->id)
            ->with('product')
            ->get();

        $totalCost = 0;
        $totalPrice = 0;
        $itemCount = 0;

        foreach ($stocks as $stock) {
            if ($stock->product) {
                $totalCost += $stock->quantity * $stock->product->cost;
                $totalPrice += $stock->quantity * $stock->product->price_without_tax;
                $itemCount++;
            }
        }

        return [
            'total_cost' => $totalCost,
            'total_price' => $totalPrice,
            'potential_profit' => $totalPrice - $totalCost,
            'item_count' => $itemCount,
        ];
    }
}
