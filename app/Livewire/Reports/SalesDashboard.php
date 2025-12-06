<?php

namespace App\Livewire\Reports;

use App\Models\Sale;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class SalesDashboard extends Component
{
    #[Url]
    public string $period = 'today';

    public string $startDate = '';
    public string $endDate = '';

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    #[Computed]
    public function dateRange(): array
    {
        return match ($this->period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'custom' => [
                $this->startDate ? now()->parse($this->startDate)->startOfDay() : now()->startOfMonth(),
                $this->endDate ? now()->parse($this->endDate)->endOfDay() : now()->endOfDay(),
            ],
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }

    #[Computed]
    public function salesSummary(): array
    {
        [$start, $end] = $this->dateRange;

        $sales = Sale::query()
            ->completed()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                COUNT(*) as count,
                SUM(total) as total,
                SUM(subtotal) as subtotal,
                SUM(tax) as tax,
                SUM(discount) as discount,
                AVG(total) as average
            ')
            ->first();

        return [
            'count' => $sales->count ?? 0,
            'total' => $sales->total ?? 0,
            'subtotal' => $sales->subtotal ?? 0,
            'tax' => $sales->tax ?? 0,
            'discount' => $sales->discount ?? 0,
            'average' => $sales->average ?? 0,
        ];
    }

    #[Computed]
    public function salesByPaymentMethod(): array
    {
        [$start, $end] = $this->dateRange;

        return Sale::query()
            ->completed()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(fn ($item) => [
                $item->payment_method => [
                    'count' => $item->count,
                    'total' => $item->total,
                ]
            ])
            ->toArray();
    }

    #[Computed]
    public function topProducts(): \Illuminate\Support\Collection
    {
        [$start, $end] = $this->dateRange;

        return DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->where('sales.status', 'completed')
            ->selectRaw('
                sale_items.product_name,
                SUM(sale_items.quantity) as quantity,
                SUM(sale_items.total) as total
            ')
            ->groupBy('sale_items.product_name')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function salesByHour(): array
    {
        [$start, $end] = $this->dateRange;

        $sales = Sale::query()
            ->completed()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('EXTRACT(HOUR FROM created_at) as hour, COUNT(*) as count, SUM(total) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $result = [];
        for ($h = 0; $h < 24; $h++) {
            $result[$h] = [
                'count' => $sales->get($h)?->count ?? 0,
                'total' => $sales->get($h)?->total ?? 0,
            ];
        }

        return $result;
    }

    #[Computed]
    public function recentSales(): \Illuminate\Support\Collection
    {
        [$start, $end] = $this->dateRange;

        return Sale::query()
            ->with(['customer', 'user'])
            ->completed()
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.reports.sales-dashboard')
            ->layout('layouts.app');
    }
}
