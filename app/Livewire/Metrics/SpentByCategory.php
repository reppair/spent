<?php

namespace App\Livewire\Metrics;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;

class SpentByCategory extends DashboardMetric
{
    #[Computed(persist: true)]
    public function stats(): Collection
    {
        if (empty($this->selectedGroups)) {
            return collect();
        }

        // Get all expenses grouped by category with totals
        $stats = Expense::query()
            ->whereIn('group_id', $this->selectedGroups)
            ->whereBetween('created_at', $this->dateRange)
            ->selectRaw('category_id, SUM(amount) as total, currency')
            ->groupBy('category_id', 'currency')
            ->get();

        if ($stats->isEmpty()) {
            return collect();
        }

        // Calculate grand total across all categories
        $grandTotal = $stats->sum('total');

        if ($grandTotal == 0) {
            return collect();
        }

        // Group by category and calculate percentages
        $grouped = $stats->groupBy('category_id')->map(function ($group) use ($grandTotal) {
            $categoryTotal = $group->sum('total');
            $expense = $group->first();
            $category = Category::find($expense->category_id);
            $currency = $expense->currency;

            return (object) [
                'name' => $category?->name ?? __('Uncategorized'),
                'total' => $categoryTotal,
                'formatted_amount' => Number::currency($categoryTotal / 100, $currency->value, app()->getLocale()),
                'percentage' => (int) round(($categoryTotal / $grandTotal) * 100),
            ];
        });

        // Sort by total descending
        return $grouped->sortByDesc('total')->values();
    }

    public function render(): View
    {
        return view('livewire.metrics.spent-by-category');
    }
}
