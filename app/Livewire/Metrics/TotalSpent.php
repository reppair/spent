<?php

namespace App\Livewire\Metrics;

use App\Models\Expense;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;

class TotalSpent extends DashboardMetric
{
    #[Computed(persist: true)]
    public function stats(): Collection
    {
        if (empty($this->selectedGroups)) {
            return collect([
                'total' => 0,
                'formatted_total' => '$0.00',
                'currency' => 'USD',
                'chart_data' => [],
            ]);
        }

        // Get all expenses for the selected period
        $expenses = Expense::query()
            ->whereIn('group_id', $this->selectedGroups)
            ->whereBetween('created_at', $this->dateRange)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total, currency')
            ->groupBy('date', 'currency')
            ->orderBy('date')
            ->get();

        if ($expenses->isEmpty()) {
            return collect([
                'total' => 0,
                'formatted_total' => '$0.00',
                'currency' => 'USD',
                'chart_data' => [],
            ]);
        }

        // Calculate grand total
        $grandTotal = $expenses->sum('total');
        $currency = $expenses->first()->currency;

        // Build chart data with date and amount for each day
        $chartData = [];
        foreach ($this->dateRange as $date) {
            $dateKey = $date->format('Y-m-d');
            $dayTotal = $expenses->where('date', $dateKey)->sum('total');
            $chartData[] = [
                'date' => $dateKey,
                'amount' => (float) ($dayTotal / 100), // Convert cents to dollars for chart
                'formatted_amount' => Number::currency($dayTotal / 100, $currency->value, app()->getLocale()),
            ];
        }

        return collect([
            'total' => $grandTotal,
            'formatted_total' => Number::currency($grandTotal / 100, $currency->value, app()->getLocale()),
            'currency' => $currency->value,
            'chart_data' => $chartData,
        ]);
    }

    public function render(): View
    {
        return view('livewire.metrics.total-spent');
    }
}
